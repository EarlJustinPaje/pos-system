<?php
// database/migrations/2025_11_10_000006_create_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        // Create custom notifications table for system-wide notifications
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['info', 'warning', 'danger', 'success'])->default('info');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['branch_id', 'type', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('notifications');
    }
};