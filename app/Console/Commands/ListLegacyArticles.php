<?php

namespace App\Console\Commands;

use App\Cms\Ops\LegacyArticleCandidates;
use Illuminate\Console\Command;

class ListLegacyArticles extends Command
{
    protected $signature = 'stemc:legacy-candidates {--fresh : Abaikan cache}';

    protected $description = 'Daftar kandidat artikel dari situs lama (judul + URL), tanpa impor.';

    public function handle(): int
    {
        $daftar = LegacyArticleCandidates::daftar((bool) $this->option('fresh'));

        if ($daftar === []) {
            $this->warn('Tidak ada tautan yang terbaca. Cek jaringan atau URL sumber.');

            return self::SUCCESS;
        }

        $this->table(
            ['Status', 'Judul', 'URL'],
            array_map(fn (array $item) => [
                $item['suspect'] ? 'CURIGA ('.$item['reason'].')' : 'ok',
                $item['title'],
                $item['url'],
            ], $daftar),
        );

        $this->info('Total: '.count($daftar).'. Tidak diimpor ke CMS. Salin yang bersih lewat panel Berita.');

        return self::SUCCESS;
    }
}
