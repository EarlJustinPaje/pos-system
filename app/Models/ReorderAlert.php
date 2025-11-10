<?php
// app/Models/ReorderAlert.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReorderAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'current_quantity',
        'reorder_point',
        'recommended_quantity',
        'priority',
        'status',
        'acknowledged_by',
        'acknowledged_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function acknowledgedByUser()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAcknowledged($query)
    {
        return $query->where('status', 'acknowledged');
    }

    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }

    public function scopeHigh($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    // Helper methods
    public function acknowledge($userId)
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_by' => $userId,
            'acknowledged_at' => now(),
        ]);
    }

    public function markAsOrdered()
    {
        $this->update(['status' => 'ordered']);
    }

    public function resolve()
    {
        $this->update(['status' => 'resolved']);
    }

    public function getPriorityBadgeClass()
    {
        return match($this->priority) {
            'low' => 'bg-info',
            'medium' => 'bg-warning',
            'high' => 'bg-danger',
            'critical' => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'acknowledged' => 'bg-info',
            'ordered' => 'bg-primary',
            'resolved' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public function getStockDeficit()
    {
        return max(0, $this->reorder_point - $this->current_quantity);
    }
}