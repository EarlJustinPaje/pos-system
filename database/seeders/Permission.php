<?php
// app/Models/Permission.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    // Permission constants
    const VIEW_DASHBOARD = 'view_dashboard';
    const VIEW_POS = 'view_pos';
    const VIEW_PRODUCTS = 'view_products';
    const CREATE_PRODUCTS = 'create_products';
    const EDIT_PRODUCTS = 'edit_products';
    const DELETE_PRODUCTS = 'delete_products';
    const VIEW_USERS = 'view_users';
    const CREATE_USERS = 'create_users';
    const EDIT_USERS = 'edit_users';
    const DELETE_USERS = 'delete_users';
    const RESET_PASSWORDS = 'reset_passwords';
    const VIEW_REPORTS = 'view_reports';
    const VIEW_AUDIT = 'view_audit';
    const MANAGE_SETTINGS = 'manage_settings';
    const MANAGE_BRANCHES = 'manage_branches';
    const MANAGE_SUPPLIERS = 'manage_suppliers';
    const MANAGE_CATEGORIES = 'manage_categories';
    const MANAGE_PROMOTIONS = 'manage_promotions';
    const IMPORT_PRODUCTS = 'import_products';
    const EXPORT_REPORTS = 'export_reports';
    const MANAGE_PAYMENT_METHODS = 'manage_payment_methods';
    const VIEW_FORECASTING = 'view_forecasting';
    const MANAGE_MARKUP = 'manage_markup';

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }

    public function isAssignedToRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }
}