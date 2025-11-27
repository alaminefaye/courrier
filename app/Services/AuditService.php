<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Enregistre une action dans les logs d'audit
     */
    public function log(string $action, Model $model, $user = null, array $details = []): AuditLog
    {
        $auditLog = new AuditLog();
        $auditLog->user_id = $user ? $user->id : auth()->id();
        $auditLog->action = $action;
        $auditLog->model_type = get_class($model);
        $auditLog->model_id = $model->id;
        $auditLog->ip_address = Request::ip();
        $auditLog->user_agent = Request::userAgent();
        $auditLog->details = !empty($details) ? $details : null;
        $auditLog->created_at = now();
        $auditLog->save();
        
        return $auditLog;
    }

    /**
     * Récupère les logs d'un modèle
     */
    public function getLogs(Model $model)
    {
        return AuditLog::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Récupère les logs d'un utilisateur
     */
    public function getUserLogs($user)
    {
        $userId = $user instanceof \App\Models\User ? $user->id : $user;
        
        return AuditLog::where('user_id', $userId)
            ->with('model')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Actions possibles
     */
    public static function getActions(): array
    {
        return [
            'view' => 'Consultation',
            'create' => 'Création',
            'update' => 'Modification',
            'delete' => 'Suppression',
            'scan_qr' => 'Scan QR Code',
            'print' => 'Impression',
            'export' => 'Export',
        ];
    }
}

