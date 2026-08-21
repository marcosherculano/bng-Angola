<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getResolvedUrlAttribute(): ?string
    {
        $url = trim((string) ($this->url ?? ''));
        if ($url === '') {
            return null;
        }

        $base = request()->getSchemeAndHttpHost() . request()->getBaseUrl();
        $base = rtrim($base, '/');

        if (preg_match('/^https?:\/\//i', $url) === 1) {
            $parts = parse_url($url);
            if (! is_array($parts)) {
                return $url;
            }

            $currentHost = (string) request()->getHost();
            $targetHost = (string) ($parts['host'] ?? '');

            if ($targetHost !== '' && strcasecmp($targetHost, $currentHost) === 0) {
                return $url;
            }

            $path = (string) ($parts['path'] ?? '');
            if ($path === '') {
                return $url;
            }

            $rebuilt = $base . '/' . ltrim($path, '/');
            if (! empty($parts['query'])) {
                $rebuilt .= '?' . $parts['query'];
            }

            return $rebuilt;
        }

        if (str_starts_with($url, '/')) {
            return $base . $url;
        }

        return $base . '/' . ltrim($url, '/');
    }
}
