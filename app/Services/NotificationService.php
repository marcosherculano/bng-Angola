<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Schema;

class NotificationService
{
    public static function notifyUser(int $userId, string $title, string $message, ?string $url = null): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Notification::query()->create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'read_at' => null,
        ]);
    }

    /**
     * @param array<int> $userIds
     */
    public static function notifyUsers(array $userIds, string $title, string $message, ?string $url = null): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        $userIds = array_values(array_unique(array_filter($userIds, function ($id) {
            return (int) $id > 0;
        })));
        if (count($userIds) === 0) {
            return;
        }

        $rows = [];
        $now = now();

        foreach ($userIds as $uid) {
            $rows[] = [
                'user_id' => (int) $uid,
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Notification::query()->insert($rows);
    }
}
