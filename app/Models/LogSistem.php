<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogSistem extends Model
{
    protected $table = 'log_sistem';

    protected $fillable = [
        'user_id', 'user_name', 'user_role', 'action', 'module',
        'description', 'ip_address', 'user_agent', 'old_data', 'new_data',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sanitize sensitive data payload.
     */
    public static function sanitizePayload(?array $data): ?array
    {
        if (!$data) return $data;

        $sensitiveKeys = ['nik', 'telp', 'no_telp', 'gol_darah', 'password', 'email'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $sensitiveKeys) && !empty($value)) {
                if ($key === 'nik' && strlen($value) >= 8) {
                    $len = strlen($value);
                    $data[$key] = substr($value, 0, 4) . str_repeat('*', $len - 8) . substr($value, -4);
                } else {
                    $data[$key] = '*** MASKED ***';
                }
            }
        }
        
        return $data;
    }

    /**
     * Helper to log an action.
     */
    public static function catat(string $action, string $module, ?string $description = null, $oldData = null, $newData = null): self
    {
        $user = auth()->user();

        return self::create([
            'user_id'    => $user?->id,
            'user_name'  => $user?->name ?? 'System',
            'user_role'  => $user?->getRoleNames()->first() ?? '-',
            'action'     => $action,
            'module'     => $module,
            'description'=> $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_data'   => self::sanitizePayload($oldData),
            'new_data'   => self::sanitizePayload($newData),
        ]);
    }
}
