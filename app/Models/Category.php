<?php
// app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'branch_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Automatically generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id'); // Global categories
        });
    }

    // Helper methods
    public function getFullNameAttribute()
    {
        if ($this->parent) {
            return $this->parent->full_name . ' > ' . $this->name;
        }
        return $this->name;
    }

    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    public function getProductsCount()
    {
        return $this->products()->active()->count();
    }
}