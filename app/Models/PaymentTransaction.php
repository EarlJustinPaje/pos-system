<?php
// app/Models/PaymentTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'payment_method_id',
        'amount',
        'reference_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Helper methods
    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }

    public function markAsRefunded()
    {
        $this->update(['status' => 'refunded']);
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            default => 'Unknown',
        };
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
            'refunded' => 'bg-secondary',
            default => 'bg-info',
        };
    }
}