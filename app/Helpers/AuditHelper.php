<?php

namespace App\Helpers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditHelper
{
    /**
     * Registra un evento en la bitácora de auditoría.
     *
     * @param string $eventType
     * @param string $targetType
     * @param int $targetId
     * @param string $description
     * @return void
     */
    public static function log(string $eventType, string $targetType, int $targetId, string $description): void
    {
        try {
            AuditLog::create([
                'user_id'     => Auth::id(),
                'event_type'  => $eventType,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'description' => $description,
                'ip_address'  => Request::ip(),
            ]);
        } catch (\Exception $e) {
            // Failsafe para no interrumpir el flujo si falla la auditoría
            logger()->error("Fallo al registrar log de auditoría: " . $e->getMessage());
        }
    }
}
