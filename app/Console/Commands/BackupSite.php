<?php

namespace App\Console\Commands;

use App\Cms\Ops\SiteBackup;
use Illuminate\Console\Command;

class BackupSite extends Command
{
    protected $signature = 'stemc:backup';

    protected $description = 'Cadangkan data CMS dan unggahan publik ke storage/app/private/backups.';

    public function handle(): int
    {
        $nama = SiteBackup::buat();
        $this->info('Cadangan dibuat: '.$nama);

        return self::SUCCESS;
    }
}
