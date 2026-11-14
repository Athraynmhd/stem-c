<?php

namespace App\Filament\Admin\Pages\System;

use App\Cms\Access\CmsAccess;
use App\Cms\Ops\SiteMaintenance;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class RunMaintenance extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $navigationLabel = 'Pemeliharaan';

    protected static ?string $title = 'Pemeliharaan situs';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 99;

    protected static ?string $slug = 'pemeliharaan';

    public static function canAccess(): bool
    {
        return CmsAccess::canPublish(Auth::user());
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function content(Schema $schema): Schema
    {
        $mediaDays = (int) config('cms.inline_edit.prune_replaced_after_days', 90);
        $super = Auth::user()?->hasRole('super_admin') === true;

        return $schema->components([
            Section::make('Arsip kegiatan')
                ->description('Menandai agenda terbit yang tanggal berakhirnya sudah lewat sebagai arsip. Sama dengan perintah stemc:archive-events.')
                ->schema([
                    Actions::make([
                        Action::make('archiveEvents')
                            ->label('Arsipkan kegiatan yang sudah lewat')
                            ->requiresConfirmation()
                            ->modalHeading('Arsipkan kegiatan?')
                            ->modalDescription('Hanya kegiatan terbit dengan tanggal selesai di masa lalu yang diarsipkan. Data tidak dihapus.')
                            ->action(function (): void {
                                $this->beritahu(SiteMaintenance::archiveExpiredEvents());
                            }),
                    ]),
                ]),
            Section::make('Pesan kontak lama')
                ->description('Menghapus pesan lebih dari 365 hari. Sama dengan stemc:prune-contacts.')
                ->schema([
                    Actions::make([
                        Action::make('pruneContacts')
                            ->label('Hapus pesan lebih dari 1 tahun')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->modalHeading('Hapus pesan lama?')
                            ->modalDescription('Pesan kontak yang lebih tua dari 365 hari akan dihapus permanen.')
                            ->action(function (): void {
                                $this->beritahu(SiteMaintenance::pruneContacts(365));
                            }),
                    ]),
                ]),
            Section::make('Berkas gambar pengganti')
                ->description('Menghapus file lama hasil ganti gambar inline yang sudah tidak dipakai dan lebih tua dari '.$mediaDays.' hari. Hanya super admin. Sama dengan stemc:prune-replaced-media.')
                ->visible($super)
                ->schema([
                    Actions::make([
                        Action::make('pruneMedia')
                            ->label('Bersihkan berkas gambar lama')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->modalHeading('Hapus berkas gambar lama?')
                            ->modalDescription('Berkas yang masih dipakai logo/konten tidak dihapus. Pemulihan dari riwayat ganti gambar gagal jika file sudah dibersihkan.')
                            ->action(function () use ($mediaDays): void {
                                $this->beritahu(SiteMaintenance::pruneReplacedMedia($mediaDays)['message']);
                            }),
                    ]),
                ]),
        ]);
    }

    private function beritahu(string $pesan): void
    {
        Notification::make()
            ->title('Selesai')
            ->body($pesan)
            ->success()
            ->send();
    }
}
