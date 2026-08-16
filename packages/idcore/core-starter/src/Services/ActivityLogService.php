<?php

namespace IdCore\CoreStarter\Services;

use IdCore\CoreStarter\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public static function record(
        int|string|null $userId,
        string $event,
        ?string $description = null,
        ?Model $subject = null,
        ?array $properties = null,
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId,
            'subject_id' => $subject?->getKey(),
            'subject_type' => $subject ? get_class($subject) : null,
            'event' => $event,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()?->ip(),
        ]);
    }

    public static function login(int|string $userId, ?string $description = null): ActivityLog
    {
        return static::record($userId, 'login', $description ?? 'Login ke aplikasi.');
    }

    public static function logout(int|string $userId, ?string $description = null): ActivityLog
    {
        return static::record($userId, 'logout', $description ?? 'Logout dari aplikasi.');
    }
}
