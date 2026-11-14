<?php

namespace App\Cms\Ops;

use App\Cms\Media\MediaLibrary;
use App\Models\Cms\MediaAsset;
use App\Models\Cms\MediaEditLog;
use App\Models\Contact\ContactMessage;
use App\Models\Content\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Tugas rutin yang biasanya dijalankan cron. Dipakai perintah artisan
 * dan tombol di panel (hosting tanpa terminal).
 */
final class SiteMaintenance
{
    public static function archiveExpiredEvents(): string
    {
        $count = Event::query()
            ->where('status', 'published')
            ->where('is_archived', false)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->update(['is_archived' => true]);

        return "Diarsipkan: {$count} kegiatan.";
    }

    public static function pruneContacts(int $days = 365): string
    {
        $days = max(30, $days);
        $count = ContactMessage::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        return "Dihapus: {$count} pesan (> {$days} hari).";
    }

    /**
     * @return array{deleted: int, skipped: int, message: string}
     */
    public static function pruneReplacedMedia(int $days = 90, bool $dryRun = false): array
    {
        if (! Schema::hasTable('media_edit_logs')) {
            return ['deleted' => 0, 'skipped' => 0, 'message' => 'Tabel media_edit_logs belum ada.'];
        }

        $days = max(14, $days);
        $cutoff = now()->subDays($days);
        $live = array_flip(MediaLibrary::livePaths());

        $candidates = MediaEditLog::query()
            ->where('action', MediaEditLog::ACTION_REPLACE)
            ->whereNotNull('old_path')
            ->where('created_at', '<', $cutoff)
            ->orderBy('id')
            ->pluck('old_path')
            ->filter(static fn (?string $path): bool => is_string($path) && $path !== '')
            ->unique()
            ->values();

        $deleted = 0;
        $skipped = 0;

        foreach ($candidates as $path) {
            if (str_starts_with($path, 'assets/') || isset($live[$path]) || ! Storage::disk('public')->exists($path)) {
                $skipped++;
                continue;
            }

            if (! $dryRun) {
                Storage::disk('public')->delete($path);

                if (Schema::hasTable('media_assets')) {
                    MediaAsset::query()->where('path', $path)->delete();
                }
            }

            $deleted++;
        }

        $label = $dryRun ? 'Akan dihapus' : 'Dihapus';

        return [
            'deleted' => $deleted,
            'skipped' => $skipped,
            'message' => "{$label}: {$deleted} berkas (> {$days} hari). Dilewati: {$skipped}.",
        ];
    }
}
