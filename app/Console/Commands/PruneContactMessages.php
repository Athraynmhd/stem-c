<?php

namespace App\Console\Commands;

use App\Cms\Ops\SiteMaintenance;
use Illuminate\Console\Command;

class PruneContactMessages extends Command
{
    protected $signature = 'stemc:prune-contacts {--days=365}';

    protected $description = 'Hapus pesan kontak lebih lama dari masa retensi (hari).';

    public function handle(): int
    {
        $this->info(SiteMaintenance::pruneContacts((int) $this->option('days')));

        return self::SUCCESS;
    }
}
