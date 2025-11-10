<?php
// app/Models/SystemNotification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'priority',
        'user_id',
        'branch_id',
        'metadata',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
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

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Helper methods
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function getTypeIcon()
    {
        return match($this->type) {
            'info' => 'bi-info-circle',
            'warning' => 'bi-exclamation-triangle',
            'danger' => 'bi-x-circle',
            'success' => 'bi-check-circle',
            default => 'bi-bell',
        };
    }

    public function getTypeBadgeClass()
    {
        return match($this->type) {
            'info' => 'bg-info',
            'warning' => 'bg-warning',
            'danger' => 'bg-danger',
            'success' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public function getPriorityLabel()
    {
        return match($this->priority) {
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
            default => 'Unknown',
        };
    }
}