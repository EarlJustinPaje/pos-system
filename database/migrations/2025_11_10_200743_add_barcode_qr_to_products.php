<?php
// database/migrations/2025_11_10_000005_add_barcode_qr_to_products.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode')->nullable()->unique()->after('product_id');
            $table->string('sku')->nullable()->unique()->after('barcode');
            $table->string('qr_code_path')->nullable()->after('sku');
            $table->string('barcode_path')->nullable()->after('qr_code_path');
            
            $table->index('barcode');
            $table->index('sku');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['barcode']);
            $table->dropIndex(['sku']);
            $table->dropColumn(['barcode', 'sku', 'qr_code_path', 'barcode_path']);
        });
    }
};