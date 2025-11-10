<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_id';

    protected $fillable = [
        'name',
        'quantity',
        'unit',
        'manufacturer',
        'price',
        'capital_price',
        'date_procured',
        'expiration_date',
        'manufactured_date',
        'is_active',
        'sold_quantity',
    ];

    protected function casts(): array
    {
        return [
            'date_procured' => 'date',
            'expiration_date' => 'date',
            'manufactured_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'product_id', 'product_id');
    }

    public function getSellingPriceAttribute()
    {
        return $this->capital_price * 1.15; // 15% markup
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', Carbon::now());
    }

    public function scopeNearExpiring($query)
    {
        return $query->where('expiration_date', '<=', Carbon::now()->addMonth())
                    ->where('expiration_date', '>', Carbon::now());
    }

    public function scopeFastMoving($query)
    {
        return $query->orderBy('sold_quantity', 'desc');
    }
}