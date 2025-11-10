<?php
// app/Models/Promotion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'discount_value',
        'buy_quantity',
        'get_quantity',
        'min_purchase_amount',
        'max_usage',
        'usage_count',
        'branch_id',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_product', 'promotion_id', 'product_id');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        });
    }

    // Helper methods
    public function isValid()
    {
        return $this->is_active 
            && now()->between($this->start_date, $this->end_date)
            && ($this->max_usage === null || $this->usage_count < $this->max_usage);
    }

    public function canBeUsed()
    {
        return $this->isValid();
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public function calculateDiscount($amount, $quantity = 1)
    {
        if (!$this->canBeUsed()) {
            return 0;
        }

        return match($this->type) {
            'percentage' => $amount * ($this->discount_value / 100),
            'fixed_amount' => min($this->discount_value, $amount),
            'buy_x_get_y' => $this->calculateBuyXGetY($amount, $quantity),
            default => 0,
        };
    }

    private function calculateBuyXGetY($amount, $quantity)
    {
        if ($quantity < $this->buy_quantity) {
            return 0;
        }

        $freeItems = floor($quantity / $this->buy_quantity) * $this->get_quantity;
        $unitPrice = $amount / $quantity;
        
        return $freeItems * $unitPrice;
    }

    public function getTypeLabel()
    {
        return match($this->type) {
            'percentage' => 'Percentage Discount',
            'fixed_amount' => 'Fixed Amount',
            'buy_x_get_y' => 'Buy X Get Y',
            'bundle' => 'Bundle Deal',
            default => 'Unknown',
        };
    }

    public function getRemainingUsage()
    {
        if ($this->max_usage === null) {
            return null;
        }
        return max(0, $this->max_usage - $this->usage_count);
    }
}