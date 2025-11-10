<?php
// app/Models/Sale.php (Updated)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'final_amount',
        'cash_received',
        'change_amount',
        'payment_status',
        'is_synced',
        'offline_id',
        'synced_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
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

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'sale_items', 'sale_id', 'product_id')
                    ->withPivot('quantity', 'unit_price', 'discount_amount', 'total_price');
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    // Scopes
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month);
    }

    public function scopeUnsynced($query)
    {
        return $query->where('is_synced', false);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Helper methods
    public function calculateTotals()
    {
        $this->total_amount = $this->saleItems->sum('total_price');
        $this->discount_amount = $this->saleItems->sum('discount_amount');
        $this->tax_amount = ($this->total_amount - $this->discount_amount) * 0.12; // 12% VAT
        $this->final_amount = $this->total_amount - $this->discount_amount + $this->tax_amount;
        $this->save();
    }

    public function markAsSynced()
    {
        $this->update([
            'is_synced' => true,
            'synced_at' => now(),
        ]);
    }

    public function getTotalItems()
    {
        return $this->saleItems->sum('quantity');
    }

    public function getPayment