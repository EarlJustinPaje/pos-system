<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'phone',
        'email',
        'is_active',
        'markup_percentage',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'markup_percentage' => 'decimal:2',
        ];
    }

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function managers()
    {
        return $this->users()->whereHas('role', function($q) {
            $q->where('name', Role::MANAGER);
        });
    }

    public function cashiers()
    {
        return $this->users()->whereHas('role', function($q) {
            $q->where('name', Role::CASHIER);
        });
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function getTotalSales($startDate = null, $endDate = null)
    {
        $query = $this->sales();
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->sum('total_amount');
    }

    public function getActiveProductsCount()
    {
        return $this->products()->where('is_active', true)->count();
    }

    public function getLowStockCount()
    {
        return $this->products()
            ->where('is_active', true)
            ->where('quantity', '<=', 10)
            ->count();
    }
}