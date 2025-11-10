<?php
// app/Models/User.php (Updated with Branch)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'is_active',
        'is_admin',
        'role_id',
        'branch_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function systemNotifications()
    {
        return $this->hasMany(SystemNotification::class);
    }

    // Role & Permission Methods
    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            return $this->role && in_array($this->role->name, $roles);
        }
        return $this->hasRole($roles);
    }

    public function hasPermission($permissionName)
    {
        return $this->role && $this->role->hasPermission($permissionName);
    }

    public function hasAnyPermission($permissions)
    {
        if (!$this->role) {
            return false;
        }

        foreach ((array)$permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin()
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isManager()
    {
        return $this->hasRole(Role::MANAGER);
    }

    public function isCashier()
    {
        return $this->hasRole(Role::CASHIER);
    }

    // Scope for active users
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for users by role
    public function scopeWithRole($query, $roleName)
    {
        return $query->whereHas('role', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    // Scope for users by branch
    public function scopeInBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    // Helper methods
    public function getUnreadNotificationsCount()
    {
        return $this->systemNotifications()->unread()->count();
    }

    public function canAccessBranch($branchId)
    {
        // Admin can access all branches
        if ($this->isAdmin()) {
            return true;
        }
        
        // Users can only access their assigned branch
        return $this->branch_id == $branchId;
    }
}