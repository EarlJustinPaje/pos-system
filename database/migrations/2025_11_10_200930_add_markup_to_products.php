<?php
// database/migrations/2025_11_10_000010_add_markup_to_products.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('markup_percentage', 5, 2)->nullable()->after('capital_price');
            $table->boolean('use_custom_markup')->default(false)->after('markup_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['markup_percentage', 'use_custom_markup']);
        });
    }
};