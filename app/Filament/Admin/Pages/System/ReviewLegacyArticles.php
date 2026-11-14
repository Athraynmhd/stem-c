<?php

namespace App\Filament\Admin\Pages\System;

use App\Cms\Access\CmsAccess;
use App\Cms\Ops\LegacyArticleCandidates;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ReviewLegacyArticles extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Kurasi situs lama';

    protected static ?string $title = 'Kandidat artikel situs lama';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 91;

    protected static ?string $slug = 'kurasi-situs-lama';

    public static function canAccess(): bool
    {
        return CmsAccess::can(Auth::user(), CmsAccess::NEWS);
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function content(Schema $schema): Schema
    {
        $sumber = config('cms.legacy_source.base_url');

        return $schema->components([
            Section::make('Kurasi manual')
                ->description('Sumber: '.$sumber.'. Hanya daftar tautan. Tidak ada impor otomatis. Lewati baris bertanda curiga (spam). Salin yang lolos ke Berita sebagai draft.')
                ->schema([
                    \Filament\Schemas\Components\Actions::make([
                        Action::make('segar')
                            ->label('Muat ulang dari sumber')
                            ->action(function (): void {
                                LegacyArticleCandidates::daftar(true);
                                Notification::make()
                                    ->title('Daftar diperbarui')
                                    ->success()
                                    ->send();
                            }),
                    ]),
                    View::make('filament.admin.pages.legacy-article-list'),
                ]),
        ]);
    }
}
