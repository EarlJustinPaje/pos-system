<?php
// app/Services/AuditService.php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuditService
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Log an action to the audit trail.
     *
     * @param string $action (e.g., 'CREATE', 'UPDATE', 'DELETE')
     * @param string $entityType (e.g., 'products', 'users')
     * @param int|null $entityId
     * @param array|null $oldValues
     * @param array|null $newValues
     */
    public function log(string $action, string $entityType, ?int $entityId, ?array $oldValues, ?array $newValues): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => strtoupper($action),
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Log error to system logs if audit logging fails
            \Log::error('Failed to create audit log: ' . $e->getMessage());
        }
    }
}