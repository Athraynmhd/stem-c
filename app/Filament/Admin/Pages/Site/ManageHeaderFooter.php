<?php

namespace App\Filament\Admin\Pages\Site;

use App\Cms\Access\CmsAccess;
use App\Cms\Chrome\SiteContent;
use App\Filament\Admin\Pages\Concerns\ManagesSettingsForm;
use App\Models\Chrome\Setting;
use App\Models\Chrome\SiteSection;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ManageHeaderFooter extends Page
{
    use ManagesSettingsForm;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $navigationLabel = 'Header & Footer';

    protected static ?string $title = 'Header & Footer';

    protected static string|\UnitEnum|null $navigationGroup = 'Situs';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'header-footer';

    /**
     * Kolom navigasi footer bawaan. Dipakai sebagai nilai awal form dan sebagai
     * cadangan bila admin belum pernah menyimpan.
     */
    public static function defaultNavColumns(): array
    {
        // Selaras dengan induk SiteMenu (bukan salinan submenu penuh).
        return [
            [
                'heading' => 'Jelajahi',
                'links' => [
                    ['label' => 'Beranda', 'url' => '/'],
                    ['label' => 'Tentang Kami', 'url' => '/profil'],
                    ['label' => 'Peneliti', 'url' => '/peneliti'],
                    ['label' => 'Kegiatan & Berita', 'url' => '/kegiatan'],
                    ['label' => 'Dokumen', 'url' => '/dokumen'],
                    ['label' => 'Kontak', 'url' => '/kontak'],
                ],
            ],
            [
                'heading' => 'Riset & Kerja Sama',
                'links' => [
                    ['label' => 'Katalog Penelitian', 'url' => '/riset'],
                    ['label' => 'Publikasi Ilmiah', 'url' => '/publikasi'],
                    ['label' => 'Produk & Inovasi', 'url' => '/riset/produk'],
                    ['label' => 'Mitra Institusi', 'url' => '/kerja-sama/mitra'],
                    ['label' => 'Ajukan Kerja Sama', 'url' => '/kerja-sama/ajukan'],
                ],
            ],
        ];
    }

    public static function canAccess(): bool
    {
        return CmsAccess::can(Auth::user(), CmsAccess::SITE_CHROME);
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->fillForm();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tombol header')
                ->description('CTA di kanan menu dan di sidenav.')
                ->schema([
                    TextInput::make('header_cta_label')->label('Label tombol')->required(),
                    TextInput::make('header_cta_url')->label('URL tombol')->required()
                        ->helperText('Boleh path relatif seperti /kontak atau URL penuh.'),
                ])
                ->columns(2),
            Section::make('CTA footer')
                ->description('Blok “Tertarik Berkolaborasi?” di atas kolom footer.')
                ->schema([
                    TextInput::make('footer_eyebrow')->label('Eyebrow'),
                    TextInput::make('footer_heading')->label('Judul')->required()->columnSpanFull(),
                    Textarea::make('footer_body')->label('Teks')->rows(3)->columnSpanFull(),
                    TextInput::make('footer_cta_label')->label('Label tombol'),
                    TextInput::make('footer_cta_url')->label('URL tombol'),
                ])
                ->columns(2),
            Section::make('Kolom tautan footer')
                ->description('Dua kolom tautan tematik di footer (mis. Jelajahi & Riset).')
                ->schema([
                    Repeater::make('footer_nav')
                        ->label('Kolom')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['heading'] ?? 'Kolom')
                        ->maxItems(3)
                        ->schema([
                            TextInput::make('heading')->label('Judul kolom')->required(),
                            Repeater::make('links')
                                ->label('Tautan')
                                ->reorderable()
                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Tautan')
                                ->schema([
                                    TextInput::make('label')->label('Teks')->required(),
                                    TextInput::make('url')->label('URL')->required()
                                        ->helperText('Path relatif (/berita) atau URL penuh.'),
                                ])
                                ->columns(2)
                                ->defaultItems(0),
                        ])
                        ->defaultItems(0),
                ]),
            Section::make('Watermark footer')
                ->schema([
                    TextInput::make('footer_watermark')->label('Teks watermark')
                        ->helperText('Teks besar samar di dasar footer. Kosongkan untuk menyembunyikan.'),
                ]),
        ]);
    }

    protected function fillForm(): void
    {
        $footer = SiteContent::section('footer.cta', onlyPublished: false);
        $nav = SiteContent::section('footer.nav', onlyPublished: false);

        $this->form->fill([
            'header_cta_label' => Setting::get('header_cta_label', 'Ajukan Kerja Sama'),
            'header_cta_url' => Setting::get('header_cta_url', '/kontak'),
            'footer_eyebrow' => $footer?->eyebrow,
            'footer_heading' => $footer?->heading,
            'footer_body' => $footer?->body,
            'footer_cta_label' => $footer?->cta_label,
            'footer_cta_url' => $footer?->cta_url,
            'footer_nav' => $nav?->meta('columns') ?: static::defaultNavColumns(),
            'footer_watermark' => Setting::get('footer_watermark', 'STEM-C'),
        ]);
    }

    protected function persist(array $data): void
    {
        Setting::set('header_cta_label', $data['header_cta_label'] ?? null, 'chrome');
        Setting::set('header_cta_url', $data['header_cta_url'] ?? null, 'chrome');
        Setting::set('footer_watermark', $data['footer_watermark'] ?? null, 'chrome');

        SiteSection::query()->updateOrCreate(
            ['key' => 'footer.cta'],
            [
                'eyebrow' => $data['footer_eyebrow'] ?? null,
                'heading' => $data['footer_heading'] ?? null,
                'body' => $data['footer_body'] ?? null,
                'cta_label' => $data['footer_cta_label'] ?? null,
                'cta_url' => $data['footer_cta_url'] ?? null,
                'is_published' => true,
            ],
        );

        SiteSection::query()->updateOrCreate(
            ['key' => 'footer.nav'],
            [
                'meta' => ['columns' => $this->bersihkanNav($data['footer_nav'] ?? [])],
                'is_published' => true,
            ],
        );

        SiteContent::forget();
    }

    /**
     * Buang baris kosong agar footer tidak menampilkan kolom/tautan tanpa isi.
     */
    private function bersihkanNav(array $columns): array
    {
        $bersih = [];

        foreach ($columns as $column) {
            $heading = trim((string) ($column['heading'] ?? ''));

            $links = [];
            foreach ($column['links'] ?? [] as $link) {
                $label = trim((string) ($link['label'] ?? ''));
                $url = trim((string) ($link['url'] ?? ''));

                if ($label !== '' && $url !== '') {
                    $links[] = ['label' => $label, 'url' => $url];
                }
            }

            if ($heading !== '' && $links !== []) {
                $bersih[] = ['heading' => $heading, 'links' => $links];
            }
        }

        return $bersih;
    }
}
