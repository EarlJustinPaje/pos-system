<?php
// app/Models/PaymentMethod.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function getTypeLabel()
    {
        return match($this->type) {
            'cash' => 'Cash',
            'card' => 'Credit/Debit Card',
            'e-wallet' => 'E-Wallet',
            'bank_transfer' => 'Bank Transfer',
            'check' => 'Check',
            default => 'Unknown',
        };
    }

    public function getTotalTransactions()
    {
        return $this->paymentTransactions()->count();
    }

    public function getTotalAmount($startDate = null, $endDate = null)
    {
        $query = $this->paymentTransactions()->where('status', 'completed');
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->sum('amount');
    }
}