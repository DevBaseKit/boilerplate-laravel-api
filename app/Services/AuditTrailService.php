<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditTrailService
{
    /**
     * Persist an audit trail record for important actions.
     */
    public function record(string $action, ?Model $auditable = null, array $metadata = []): void
    {
        $request = request();

        AuditLog::create([
            'actor_id' => Auth::guard('api')->id() ?? Auth::id(),
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'request_id' => $request?->attributes->get('request_id'),
            'route' => $request?->path(),
            'method' => $request?->method(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
