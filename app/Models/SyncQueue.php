<?php
// app/Models/SyncQueue.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'entity_type',
        'entity_id',
        'action',
        'data',
        'status',
        'error_message',
        'retry_count',
        'synced_at',
    ];

    protected $casts = [
        'data' => 'array',
        'synced_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(SyncLog::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByEntityType($query, $type)
    {
        return $query->where('entity_type', $type);
    }

    // Helper methods
    public function markAsSyncing()
    {
        $this->update(['status' => 'syncing']);
    }

    public function markAsSynced()
    {
        $this->update([
            'status' => 'synced',
            'synced_at' => now(),
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->increment('retry_count');
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function canRetry($maxRetries = 3)
    {
        return $this->retry_count < $maxRetries;
    }

    public function addLog($status, $message = null, $response = null)
    {
        return $this->syncLogs()->create([
            'status' => $status,
            'message' => $message,
            'response' => $response,
        ]);
    }
}