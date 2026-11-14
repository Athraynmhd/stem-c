<?php

namespace App\Console\Commands;

use App\Cms\Ops\SiteMaintenance;
use Illuminate\Console\Command;

class ArchiveExpiredEvents extends Command
{
    protected $signature = 'stemc:archive-events';

    protected $description = 'Arsipkan agenda yang sudah lewat tanggal berakhirnya.';

    public function handle(): int
    {
        $this->info(SiteMaintenance::archiveExpiredEvents());

        return self::SUCCESS;
    }
}
