<?php
// app/Models/ProductImport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'filename',
        'file_path',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'status',
        'errors',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
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

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Helper methods
    public function markAsProcessing()
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed($errors = null)
    {
        $this->update([
            'status' => 'failed',
            'errors' => $errors,
            'completed_at' => now(),
        ]);
    }

    public function incrementProcessed()
    {
        $this->increment('processed_rows');
    }

    public function incrementSuccessful()
    {
        $this->increment('successful_rows');
    }

    public function incrementFailed()
    {
        $this->increment('failed_rows');
    }

    public function getProgressPercentage()
    {
        if ($this->total_rows == 0) {
            return 0;
        }
        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }

    public function getSuccessRate()
    {
        if ($this->processed_rows == 0) {
            return 0;
        }
        return round(($this->successful_rows / $this->processed_rows) * 100, 2);
    }
}