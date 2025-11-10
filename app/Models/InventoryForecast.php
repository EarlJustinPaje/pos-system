<?php
// app/Models/InventoryForecast.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'forecast_date',
        'predicted_demand',
        'current_stock',
        'recommended_order_quantity',
        'confidence_score',
        'historical_data',
        'seasonality',
        'notes',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'confidence_score' => 'decimal:2',
        'historical_data' => 'array',
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

    // Scopes
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('forecast_date', '>=', now())
                    ->where('forecast_date', '<=', now()->addDays($days))
                    ->orderBy('forecast_date');
    }

    // Helper methods
    public function isStockSufficient()
    {
        return $this->current_stock >= $this->predicted_demand;
    }

    public function getStockDeficit()
    {
        return max(0, $this->predicted_demand - $this->current_stock);
    }

    public function getDaysUntilStockout()
    {
        if ($this->current_stock <= 0 || $this->predicted_demand <= 0) {
            return 0;
        }

        $dailyDemand = $this->predicted_demand / 30; // Assuming monthly forecast
        return floor($this->current_stock / $dailyDemand);
    }

    public function getConfidenceLevel()
    {
        if ($this->confidence_score >= 80) {
            return 'high';
        } elseif ($this->confidence_score >= 60) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    public function getSeasonalityLabel()
    {
        return match($this->seasonality) {
            'none' => 'No Seasonality',
            'weekly' => 'Weekly Pattern',
            'monthly' => 'Monthly Pattern',
            'yearly' => 'Yearly Pattern',
            default => 'Unknown',
        };
    }
}