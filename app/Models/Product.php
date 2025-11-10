<?php
// app/Models/Product.php (Updated)

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
        'barcode',
        'sku',
        'qr_code_path',
        'barcode_path',
        'branch_id',
        'category_id',
        'supplier_id',
        'quantity',
        'reorder_point',
        'reorder_quantity',
        'auto_reorder',
        'unit',
        'manufacturer',
        'price',
        'capital_price',
        'markup_percentage',
        'use_custom_markup',
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
            'auto_reorder' => 'boolean',
            'use_custom_markup' => 'boolean',
            'capital_price' => 'decimal:2',
            'markup_percentage' => 'decimal:2',
            'price' => 'decimal:2',
        ];
    }

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'product_id', 'product_id');
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_product', 'product_id', 'promotion_id');
    }

    public function inventoryForecasts()
    {
        return $this->hasMany(InventoryForecast::class, 'product_id', 'product_id');
    }

    public function reorderAlerts()
    {
        return $this->hasMany(ReorderAlert::class, 'product_id', 'product_id');
    }

    // Accessors
    public function getSellingPriceAttribute()
    {
        if ($this->use_custom_markup && $this->markup_percentage !== null) {
            return $this->capital_price * (1 + ($this->markup_percentage / 100));
        }
        
        // Use branch markup if available
        if ($this->branch && $this->branch->markup_percentage) {
            return $this->capital_price * (1 + ($this->branch->markup_percentage / 100));
        }
        
        // Default 15% markup
        return $this->capital_price * 1.15;
    }

    public function getActualMarkupPercentageAttribute()
    {
        if ($this->use_custom_markup && $this->markup_percentage !== null) {
            return $this->markup_percentage;
        }
        
        return $this->branch ? $this->branch->markup_percentage : 15.00;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', Carbon::now());
    }

    public function scopeNearExpiring($query, $days = 30)
    {
        return $query->where('expiration_date', '<=', Carbon::now()->addDays($days))
                    ->where('expiration_date', '>', Carbon::now());
    }

    public function scopeFastMoving($query)
    {
        return $query->orderBy('sold_quantity', 'desc');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_point');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByBarcode($query, $barcode)
    {
        return $query->where('barcode', $barcode);
    }

    public function scopeBySku($query, $sku)
    {
        return $query->where('sku', $sku);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('manufacturer', 'like', "%{$search}%");
        });
    }

    // Helper methods
    public function needsReorder()
    {
        return $this->quantity <= $this->reorder_point;
    }

    public function isExpired()
    {
        return $this->expiration_date && $this->expiration_date < now();
    }

    public function isNearExpiry($days = 30)
    {
        return $this->expiration_date 
            && $this->expiration_date <= now()->addDays($days)
            && $this->expiration_date > now();
    }

    public function getDaysUntilExpiry()
    {
        if (!$this->expiration_date) {
            return null;
        }
        return now()->diffInDays($this->expiration_date, false);
    }

    public function getStockStatus()
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->needsReorder()) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    public function getStockStatusLabel()
    {
        return match($this->getStockStatus()) {
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            'in_stock' => 'In Stock',
            default => 'Unknown',
        };
    }

    public function getStockStatusBadgeClass()
    {
        return match($this->getStockStatus()) {
            'out_of_stock' => 'bg-danger',
            'low_stock' => 'bg-warning',
            'in_stock' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public function getActivePromotions()
    {
        return $this->promotions()->active()->get();
    }

    public function hasActivePromotion()
    {
        return $this->promotions()->active()->exists();
    }

    public function calculatePrice($quantity = 1)
    {
        $basePrice = $this->selling_price * $quantity;
        
        // Check for active promotions
        $promotion = $this->getActivePromotions()->first();
        if ($promotion) {
            $discount = $promotion->calculateDiscount($basePrice, $quantity);
            return $basePrice - $discount;
        }
        
        return $basePrice;
    }
}