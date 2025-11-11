<?php
// app/Jobs/ProcessProductImport.php

namespace App\Jobs;

use App\Models\ProductImport;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ProcessProductImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $import;

    /**
     * Create a new job instance.
     */
    public function __construct(ProductImport $import)
    {
        $this->import = $import;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $import = $this->import;
        
        try {
            $import->markAsProcessing();

            $rows = Excel::toCollection(null, $import->file_path, 'local')[0];
            $headings = $rows->shift()->map(fn($item) => strtolower(trim($item)));
            
            $import->update(['total_rows' => $rows->count()]);
            
            $errors = [];

            foreach ($rows as $rowIndex => $row) {
                $data = $row->toArray();
                $rowData = array_combine($headings->toArray(), $data);

                $rowNumber = $rowIndex + 2; // +1 for 0-index, +1 for header
                
                try {
                    $import->incrementProcessed();

                    // 1. Validate data
                    $validator = Validator::make($rowData, [
                        'name' => 'required|string|max:255',
                        'quantity' => 'required|integer|min:0',
                        'capital_price' => 'required|numeric|min:0',
                        'unit' => 'required|string',
                        'manufacturer' => 'required|string',
                        'barcode' => 'nullable|string|unique:products,barcode',
                        'sku' => 'nullable|string|unique:products,sku',
                        'category_name' => 'nullable|string',
                        'supplier_code' => 'nullable|string',
                    ]);

                    if ($validator->fails()) {
                        throw new \Exception(implode(', ', $validator->errors()->all()));
                    }

                    // 2. Find relations (Category, Supplier)
                    $categoryId = null;
                    if (!empty($rowData['category_name'])) {
                        $category = Category::firstOrCreate(['name' => $rowData['category_name']]);
                        $categoryId = $category->id;
                    }

                    $supplierId = null;
                    if (!empty($rowData['supplier_code'])) {
                        $supplier = Supplier::where('code', $rowData['supplier_code'])->first();
                        if ($supplier) {
                            $supplierId = $supplier->id;
                        }
                    }

                    // 3. Update or Create Product
                    // We'll use barcode or SKU as the unique identifier if provided
                    $uniqueIdentifier = null;
                    if (!empty($rowData['barcode'])) {
                        $uniqueIdentifier = ['barcode' => $rowData['barcode']];
                    } elseif (!empty($rowData['sku'])) {
                        $uniqueIdentifier = ['sku' => $rowData['sku']];
                    }

                    $productData = [
                        'name' => $rowData['name'],
                        'quantity' => $rowData['quantity'],
                        'capital_price' => $rowData['capital_price'],
                        'unit' => $rowData['unit'],
                        'manufacturer' => $rowData['manufacturer'],
                        'category_id' => $categoryId,
                        'supplier_id' => $supplierId,
                        'branch_id' => $import->branch_id,
                        'barcode' => $rowData['barcode'] ?? null,
                        'sku' => $rowData['sku'] ?? null,
                        'markup_percentage' => $rowData['markup_percentage'] ?? null,
                        'use_custom_markup' => !empty($rowData['markup_percentage']),
                        'date_procured' => $rowData['date_procured'] ?? now(),
                        'expiration_date' => $rowData['expiration_date'] ?? null,
                        'manufactured_date' => $rowData['manufactured_date'] ?? now(),
                    ];

                    if ($uniqueIdentifier) {
                        Product::updateOrCreate($uniqueIdentifier, $productData);
                    } else {
                        // If no barcode/sku, just create new
                        Product::create($productData);
                    }

                    $import->incrementSuccessful();

                } catch (\Exception $e) {
                    $errors[$rowNumber] = $e->getMessage();
                    $import->incrementFailed();
                }
            }

            $import->update(['errors' => $errors]);
            $import->markAsCompleted();

        } catch (\Exception $e) {
            $import->markAsFailed([
                'general_error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}