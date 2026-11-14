<?php

namespace App\Models\Cms;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Jejak CRUD konten dari panel admin (dan model yang memakai LogsCmsActivity).
 * Baris bersifat append-only — tidak ada update/delete lewat aplikasi.
 */
class ContentActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'changes',
        'ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    public static function catat(string $action, Model $subject, ?array $changes = null, ?Request $request = null): self
    {
        $request ??= request();
        $user = Auth::user();
        $label = $subject->getAttribute('title')
            ?? $subject->getAttribute('name')
            ?? class_basename($subject).'#'.$subject->getKey();

        return static::query()->create([
            'user_id' => $user instanceof User ? $user->getKey() : null,
            'user_name' => $user instanceof User ? $user->name : 'sistem',
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'subject_label' => is_scalar($label) ? (string) $label : class_basename($subject),
            'changes' => $changes,
            'ip' => $request?->ip(),
            'user_agent' => mb_substr((string) $request?->userAgent(), 0, 255) ?: null,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
