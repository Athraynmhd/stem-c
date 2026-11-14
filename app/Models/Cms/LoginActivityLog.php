<?php

namespace App\Models\Cms;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LoginActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'user_name',
        'email',
        'event',
        'ip',
        'user_agent',
    ];

    public static function catat(string $event, ?User $user = null, ?string $email = null, ?Request $request = null): ?self
    {
        if (! Schema::hasTable('login_activity_logs')) {
            return null;
        }

        $request ??= request();

        return static::query()->create([
            'user_id' => $user?->getKey(),
            'user_name' => $user?->name,
            'email' => $email ?? $user?->email,
            'event' => $event,
            'ip' => $request?->ip(),
            'user_agent' => mb_substr((string) $request?->userAgent(), 0, 255) ?: null,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
