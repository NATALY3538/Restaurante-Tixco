<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'module',
        'details_json',
        'ip_address'
    ];

    protected $casts = [
        'details_json' => 'array',
    ];

    /**
     * Helper to log critical system events cleanly
     */
    public static function record(string $action, string $module, array $details = [], ?string $userName = null): self
    {
        return self::create([
            'user_id' => auth()->id() ?? null,
            'user_name' => $userName ?? (auth()->user()->name ?? 'Admin / Gerente'),
            'action' => $action,
            'module' => $module,
            'details_json' => $details,
            'ip_address' => request()->ip() ?? '127.0.0.1'
        ]);
    }
}
