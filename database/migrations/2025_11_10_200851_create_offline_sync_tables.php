<?php
// database/migrations/2025_11_10_000008_create_offline_sync_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('entity_type'); // sales, products, etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->enum('action', ['create', 'update', 'delete'])->default('create');
            $table->json('data');
            $table->enum('status', ['pending', 'syncing', 'synced', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->index(['entity_type', 'entity_id', 'status']);
            $table->index(['branch_id', 'status', 'created_at']);
        });

        // Create sync log table
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sync_queue_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['started', 'success', 'failed'])->default('started');
            $table->text('message')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
            
            $table->index(['sync_queue_id', 'status']);
        });

        // Add sync metadata to sales
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('is_synced')->default(true)->after('payment_status');
            $table->string('offline_id')->nullable()->unique()->after('is_synced');
            $table->timestamp('synced_at')->nullable()->after('offline_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['is_synced', 'offline_id', 'synced_at']);
        });
        
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('sync_queues');
    }
};