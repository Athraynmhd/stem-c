<?php

namespace App\Models\Cms;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

/**
 * Jejak setiap penggantian gambar dari halaman publik.
 *
 * Baris di sini tidak pernah diubah atau dihapus lewat aplikasi: isinya jadi
 * bukti siapa menimpa apa, dan `old_path` yang tetap tersimpan di disk membuat
 * penggantian keliru bisa dipulihkan.
 */
class MediaEditLog extends Model
{
    public const ACTION_REPLACE = 'replace';

    public const ACTION_RESTORE = 'restore';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'user_name',
        'target',
        'old_path',
        'new_path',
        'action',
        'ip',
        'user_agent',
    ];

    public static function catat(
        User $user,
        string $target,
        ?string $lama,
        string $baru,
        string $action = self::ACTION_REPLACE,
        ?Request $request = null,
    ): self {
        return static::query()->create([
            'user_id' => $user->getKey(),
            'user_name' => $user->name,
            'target' => $target,
            'old_path' => $lama,
            'new_path' => $baru,
            'action' => $action,
            'ip' => $request?->ip(),
            'user_agent' => mb_substr((string) $request?->userAgent(), 0, 255) ?: null,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bisa dipulihkan hanya bila ada versi sebelumnya untuk dikembalikan.
     */
    public function dapatDipulihkan(): bool
    {
        return $this->old_path !== null && $this->action === self::ACTION_REPLACE;
    }
}
