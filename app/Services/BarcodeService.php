<?php
// app/Services/BarcodeService.php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Illuminate\Support\Str;

class BarcodeService
{
    public function __construct()
    {
        // Ensure directories exist
        Storage::disk('public')->makeDirectory('barcodes');
        Storage::disk('public')->makeDirectory('qrcodes');
    }

    // Generates a barcode image and saves its path
    public function generateBarcodeImage(Product $product): void
    {
        $barcode = $product->barcode;
        if (empty($barcode)) {
            return;
        }

        // Delete old file if it exists
        if ($product->barcode_path && Storage::disk('public')->exists($product->barcode_path)) {
            Storage::disk('public')->delete($product->barcode_path);
        }

        $d = new DNS1D();
        $d->setStorPath(storage_path('app/public/barcodes/'));
        $png = $d->getBarcodePNG($barcode, 'C128');
        
        $fileName = 'barcode-' . $product->product_id . '-' . $barcode . '.png';
        $path = 'barcodes/' . $fileName;

        Storage::disk('public')->put($path, base64_decode($png));
        $product->barcode_path = $path;
        $product->saveQuietly(); // Save without triggering events
    }

    // Generates a QR code image and saves its path
    public function generateQRCode(Product $product): void
    {
        $barcode = $product->barcode; // We'll use the barcode for the QR content
        if (empty($barcode)) {
            return;
        }

        // Delete old file if it exists
        if ($product->qr_code_path && Storage::disk('public')->exists($product->qr_code_path)) {
            Storage::disk('public')->delete($product->qr_code_path);
        }

        $d = new DNS2D();
        $d->setStorPath(storage_path('app/public/qrcodes/'));
        $png = $d->getBarcodePNG($barcode, 'QRCODE');
        
        $fileName = 'qr-' . $product->product_id . '-' . $barcode . '.png';
        $path = 'qrcodes/' . $fileName;

        Storage::disk('public')->put($path, base64_decode($png));
        $product->qr_code_path = $path;
        $product->saveQuietly();
    }

    // Deletes barcode/QR files when a product is deleted
    public function deleteBarcodeFiles(Product $product): void
    {
        if ($product->barcode_path && Storage::disk('public')->exists($product->barcode_path)) {
            Storage::disk('public')->delete($product->barcode_path);
        }
        if ($product->qr_code_path && Storage::disk('public')->exists($product->qr_code_path)) {
            Storage::disk('public')->delete($product->qr_code_path);
        }
    }

    // Generates a unique 13-digit EAN-13 style barcode
    public function generateUniqueBarcode(): string
    {
        do {
            $barcode = (string) mt_rand(100000000000, 999999999999);
            // You might want to add a check digit calculation for EAN-13
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    // Generates a unique SKU
    public function generateUniqueSKU(string $productName): string
    {
        $baseSku = Str::upper(Str::slug($productName, '-'));
        $sku = $baseSku;
        $counter = 1;

        while (Product::where('sku', $sku)->exists()) {
            $sku = $baseSku . '-' . $counter++;
        }

        return $sku;
    }
}