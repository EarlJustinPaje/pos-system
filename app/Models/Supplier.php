<?php
// app/Models/Supplier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'country',
        'tax_id',
        'payment_terms',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function getProductsCount()
    {
        return $this->products()->count();
    }

    public function getActiveProductsCount()
    {
        return $this->products()->active()->count();
    }

    public function getTotalSupplied()
    {
        return $this->products()->sum('quantity');
    }

    public function getPaymentTermsLabel()
    {
        return match($this->payment_terms) {
            'cash' => 'Cash',
            'credit_7' => '7 Days Credit',
            'credit_15' => '15 Days Credit',
            'credit_30' => '30 Days Credit',
            'credit_60' => '60 Days Credit',
            default => 'Unknown',
        };
    }
}