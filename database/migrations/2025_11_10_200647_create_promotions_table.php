<?php
// database/migrations/2025_11_10_000003_create_promotions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount', 'buy_x_get_y', 'bundle'])->default('percentage');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->integer('buy_quantity')->nullable(); // For buy X get Y
            $table->integer('get_quantity')->nullable(); // For buy X get Y
            $table->decimal('min_purchase_amount', 10, 2)->nullable();
            $table->integer('max_usage')->nullable();
            $table->integer('usage_count')->default(0);
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->timestamps();
            
            $table->index(['branch_id', 'is_active', 'start_date', 'end_date']);
        });

        // Promotion products pivot table
        Schema::create('promotion_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['promotion_id', 'product_id']);
        });

        // Add discount columns to sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)->default(0)->after('unit_price');
            $table->foreignId('promotion_id')->nullable()->after('discount_amount')->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropColumn(['discount_amount', 'promotion_id']);
        });
        
        Schema::dropIfExists('promotion_product');
        Schema::dropIfExists('promotions');
    }
};