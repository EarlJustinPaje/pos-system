<?php
// database/migrations/2025_11_10_000004_create_payment_methods_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['cash', 'card', 'e-wallet', 'bank_transfer', 'check'])->default('cash');
            $table->text('description')->nullable();
            $table->json('config')->nullable(); // For API keys, account numbers, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create payment transactions table
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained()->onDelete('restrict');
            $table->decimal('amount', 10, 2);
            $table->string('reference_number')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['sale_id', 'payment_method_id', 'status']);
        });

        // Modify sales table to support multiple payment methods
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('final_amount', 10, 2)->after('tax_amount');
            $table->string('payment_status')->default('completed')->after('change_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'tax_amount', 'final_amount', 'payment_status']);
        });
        
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payment_methods');
    }
};