<?php
// app/Http/Controllers/ProductImportController.php

namespace App\Http\Controllers;

use App\Models\ProductImport;
use App\Services\ProductImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImportController extends Controller
{
    protected $importService;

    public function __construct(ProductImportService $importService)
    {
        $this->middleware('auth');
        $this->middleware('permission:import_products');
        $this->importService = $importService;
    }

    public function index()
    {
        $imports = ProductImport::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('products.imports.index', compact('imports'));
    }

    public function create()
    {
        return view('products.imports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('imports', $filename, 'local');

            $import = ProductImport::create([
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id,
                'filename' => $filename,
                'file_path' => $path,
                'status' => 'pending',
            ]);

            // Dispatch import job
            \App\Jobs\ProcessProductImport::dispatch($import);

            return redirect()->route('products.imports.show', $import)
                ->with('success', 'Import started. You will be notified when it completes.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }

    public function show(ProductImport $import)
    {
        // Check authorization
        if ($import->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('products.imports.show', compact('import'));
    }

    public function download(ProductImport $import)
    {
        // Check authorization
        if ($import->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        if (!Storage::exists($import->file_path)) {
            return redirect()->back()
                ->with('error', 'Import file not found');
        }

        return Storage::download($import->file_path, $import->filename);
    }

    public function downloadTemplate()
    {
        $headers = [
            'name',
            'barcode',
            'sku',
            'category_name',
            'supplier_code',
            'quantity',
            'reorder_point',
            'reorder_quantity',
            'unit',
            'manufacturer',
            'capital_price',
            'markup_percentage',
            'date_procured',
            'expiration_date',
            'manufactured_date',
        ];

        $filename = 'product_import_template.csv';
        
        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            // Add sample row
            fputcsv($file, [
                'Sample Product',
                '1234567890123',
                'PROD-001',
                'Electronics',
                'SUP-001',
                '100',
                '10',
                '50',
                'pcs',
                'Sample Manufacturer',
                '50.00',
                '20.00',
                date('Y-m-d'),
                date('Y-m-d', strtotime('+1 year')),
                date('Y-m-d', strtotime('-1 month')),
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function retry(ProductImport $import)
    {
        // Check authorization
        if ($import->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($import->status !== 'failed') {
            return redirect()->back()
                ->with('error', 'Only failed imports can be retried');
        }

        $import->update([
            'status' => 'pending',
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'errors' => null,
        ]);

        // Dispatch import job
        \App\Jobs\ProcessProductImport::dispatch($import);

        return redirect()->route('products.imports.show', $import)
            ->with('success', 'Import retry started');
    }

    public function destroy(ProductImport $import)
    {
        // Check authorization
        if ($import->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        // Delete file
        if (Storage::exists($import->file_path)) {
            Storage::delete($import->file_path);
        }

        $import->delete();

        return redirect()->route('products.imports.index')
            ->with('success', 'Import deleted successfully');
    }

    public function getProgress(ProductImport $import)
    {
        // Check authorization
        if ($import->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        return response()->json([
            'status' => $import->status,
            'progress' => $import->getProgressPercentage(),
            'total_rows' => $import->total_rows,
            'processed_rows' => $import->processed_rows,
            'successful_rows' => $import->successful_rows,
            'failed_rows' => $import->failed_rows,
            'success_rate' => $import->getSuccessRate(),
        ]);
    }
}