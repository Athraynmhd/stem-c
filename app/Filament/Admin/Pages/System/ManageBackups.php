<?php

namespace App\Filament\Admin\Pages\System;

use App\Cms\Ops\SiteBackup;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManageBackups extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Cadangan';

    protected static ?string $title = 'Cadangan & pulihkan';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'cadangan';

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('super_admin') === true;
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Buat cadangan')
                ->description('Menyimpan isi basis data (tanpa sesi/cache) dan folder storage/app/public. Berkas .env tidak ikut. Simpan salinan di luar server setelah unduh.')
                ->schema([
                    \Filament\Schemas\Components\Actions::make([
                        Action::make('buat')
                            ->label('Buat cadangan sekarang')
                            ->requiresConfirmation()
                            ->modalHeading('Buat cadangan?')
                            ->modalDescription('Proses singkat pada data seeder; pada unggahan banyak bisa memakan waktu.')
                            ->action(function (): void {
                                $nama = SiteBackup::buat();
                                Notification::make()
                                    ->title('Cadangan siap')
                                    ->body($nama)
                                    ->success()
                                    ->send();
                            }),
                    ]),
                ]),
            Section::make('Berkas tersimpan')
                ->description('Maksimal 12 cadangan terbaru. Pulihkan menimpa data aplikasi saat ini.')
                ->schema([
                    View::make('filament.admin.pages.backup-list'),
                ]),
        ]);
    }

    public function unduh(string $nama): BinaryFileResponse
    {
        abort_unless(static::canAccess(), 403);

        return response()->download(SiteBackup::pathAbsolut($nama));
    }

    public function pulihkan(string $nama): void
    {
        abort_unless(static::canAccess(), 403);

        $pesan = SiteBackup::pulihkan($nama);
        Notification::make()->title('Dipulihkan')->body($pesan)->success()->send();
    }

    public function hapusBerkas(string $nama): void
    {
        abort_unless(static::canAccess(), 403);

        SiteBackup::hapus($nama);
        Notification::make()->title('Dihapus')->body($nama)->success()->send();
    }
}
