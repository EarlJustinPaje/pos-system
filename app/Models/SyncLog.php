<?php
// app/Models/SyncLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sync_queue_id',
        'status',
        'message',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    // Relationships
    public function syncQueue()
    {
        return $this->belongsTo(SyncQueue::class);
    }
}