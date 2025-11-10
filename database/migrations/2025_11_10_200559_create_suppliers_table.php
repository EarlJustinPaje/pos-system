<?php
// database/migrations/2025_11_10_000002_create_suppliers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Philippines');
            $table->string('tax_id')->nullable();
            $table->enum('payment_terms', ['cash', 'credit_7', 'credit_15', 'credit_30', 'credit_60'])->default('cash');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'name']);
        });

        // Add supplier_id to products table
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('category_id')->constrained()->onDelete('set null');
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
        
        Schema::dropIfExists('suppliers');
    }
};