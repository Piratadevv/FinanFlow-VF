<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogger
{
    public static function log(
        string $action,
        string $category,
        string $severity = 'info',
        ?string $message = null,
        ?string $description = null,
        ?string $entityType = null,
        ?string $entityId = null,
        ?array $changes = null,
        ?array $metadata = null
    ): Log {
        $request = request();

        $meta = array_merge($metadata ?? [], [
            'ip' => $request?->ip() ?? '127.0.0.1',
            'userAgent' => $request?->userAgent() ?? 'Unknown',
        ]);

        return Log::create([
            'id' => 'log_' . now()->format('YmdHis') . '_' . Str::random(5),
            'timestamp' => now(),
            'action' => $action,
            'category' => $category,
            'severity' => $severity,
            'message' => $message,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => Auth::check() ? Auth::user()->username : null,
            'changes' => $changes,
            'metadata' => $meta,
        ]);
    }
}
