<?php

namespace App\Console\Commands;

use App\Cms\Ops\SiteMaintenance;
use Illuminate\Console\Command;

class PruneReplacedMedia extends Command
{
    protected $signature = 'stemc:prune-replaced-media
                            {--days= : Usia minimum berkas pengganti (hari)}
                            {--dry-run : Hanya tampilkan path yang akan dihapus}';

    protected $description = 'Hapus berkas gambar lama hasil ganti inline setelah masa retensi.';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('cms.inline_edit.prune_replaced_after_days', 90));
        $result = SiteMaintenance::pruneReplacedMedia($days, (bool) $this->option('dry-run'));
        $this->info($result['message']);

        return self::SUCCESS;
    }
}
