<?php
// database/migrations/2025_11_10_000007_create_inventory_forecast_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('forecast_date');
            $table->integer('predicted_demand');
            $table->integer('current_stock');
            $table->integer('recommended_order_quantity');
            $table->decimal('confidence_score', 5, 2)->default(0); // 0-100%
            $table->json('historical_data')->nullable();
            $table->enum('seasonality', ['none', 'weekly', 'monthly', 'yearly'])->default('none');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['product_id', 'forecast_date']);
            $table->index(['branch_id', 'forecast_date']);
        });

        // Create reorder alerts table
        Schema::create('reorder_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('current_quantity');
            $table->integer('reorder_point');
            $table->integer('recommended_quantity');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'acknowledged', 'ordered', 'resolved'])->default('pending');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            
            $table->index(['product_id', 'status']);
            $table->index(['branch_id', 'priority', 'status']);
        });

        // Add reorder settings to products
        Schema::table('products', function (Blueprint $table) {
            $table->integer('reorder_point')->default(10)->after('quantity');
            $table->integer('reorder_quantity')->default(50)->after('reorder_point');
            $table->boolean('auto_reorder')->default(false)->after('reorder_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['reorder_point', 'reorder_quantity', 'auto_reorder']);
        });
        
        Schema::dropIfExists('reorder_alerts');
        Schema::dropIfExists('inventory_forecasts');
    }
};