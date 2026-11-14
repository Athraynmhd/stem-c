<?php

namespace App\Filament\Admin\Pages\Site;

use App\Filament\Admin\Pages\Concerns\ManagesSettingsForm;
use App\Models\Chrome\SiteSection;
use App\Cms\Access\CmsAccess;
use App\Cms\Chrome\SiteContent;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ManagePageChrome extends Page
{
    use ManagesSettingsForm;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Banner & Intro Halaman';

    protected static ?string $title = 'Banner & Intro Halaman';

    protected static string|\UnitEnum|null $navigationGroup = 'Situs';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'banner-halaman';

    /** @var array<string, string> */
    private const PAGES = [
        'berita' => 'Berita',
        'kegiatan' => 'Kegiatan',
        'publikasi' => 'Publikasi',
        'dokumentasi' => 'Dokumentasi',
        'dokumen' => 'Dokumen',
        'kontak' => 'Kontak',
    ];

    /**
     * Halaman yang judul bannernya mengikuti isi record, jadi admin hanya
     * mengatur gambarnya.
     *
     * @var array<string, array{label: string, hint: string}>
     */
    private const IMAGE_ONLY = [
        'profil' => [
            'label' => 'Profil',
            'hint' => 'Judul banner otomatis memakai nama topik profil yang dibuka.',
        ],
        'berita_detail' => [
            'label' => 'Detail Berita',
            'hint' => 'Dipakai bila berita belum punya gambar sampul sendiri.',
        ],
        'kegiatan_detail' => [
            'label' => 'Detail Kegiatan',
            'hint' => 'Dipakai bila kegiatan belum punya gambar sampul sendiri.',
        ],
    ];

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
        $tabs = [];

        foreach (self::PAGES as $key => $label) {
            $components = [
                Section::make('Banner')->schema([
                    Hidden::make("{$key}_banner.existing_image"),
                    TextInput::make("{$key}_banner.heading")->label('Judul banner'),
                    FileUpload::make("{$key}_banner.image")->label('Gambar banner')->image()->directory('site/banners'),
                ])->columns(2),
                Section::make('Intro')->schema([
                    TextInput::make("{$key}_intro.eyebrow")->label('Eyebrow'),
                    TextInput::make("{$key}_intro.heading")->label('Judul'),
                    TextInput::make("{$key}_intro.heading_highlight")->label('Bagian ditebalkan'),
                    Textarea::make("{$key}_intro.body")->label('Paragraf intro')->rows(3)->columnSpanFull(),
                ])->columns(2),
            ];

            if ($key === 'kegiatan') {
                $components[] = Section::make('Section “Telah Terlaksana”')->schema([
                    TextInput::make('kegiatan_past.eyebrow')->label('Eyebrow'),
                    Textarea::make('kegiatan_past.body')->label('Paragraf')->rows(3)->columnSpanFull(),
                ])->columns(2);
            }

            $tabs[] = Tab::make($label)->schema($components);
        }

        $imageOnly = [];

        foreach (self::IMAGE_ONLY as $key => $meta) {
            $imageOnly[] = Section::make($meta['label'])->schema([
                Hidden::make("{$key}_banner.existing_image"),
                FileUpload::make("{$key}_banner.image")
                    ->label('Gambar banner')
                    ->helperText($meta['hint'])
                    ->image()
                    ->directory('site/banners'),
            ]);
        }

        $tabs[] = Tab::make('Profil & Detail')->schema($imageOnly);

        return $schema->components([
            Tabs::make('Halaman')->persistTabInQueryString()->tabs($tabs)->columnSpanFull(),
        ]);
    }

    protected function fillForm(): void
    {
        $data = [];

        foreach (self::PAGES as $key => $label) {
            $banner = SiteContent::section("{$key}.banner", onlyPublished: false);
            $intro = SiteContent::section("{$key}.intro", onlyPublished: false);

            $data["{$key}_banner"] = [
                'heading' => $banner?->heading,
                'existing_image' => $banner?->image,
                'image' => $this->uploadState($banner?->image),
            ];
            $data["{$key}_intro"] = [
                'eyebrow' => $intro?->eyebrow,
                'heading' => $intro?->heading,
                'heading_highlight' => $intro?->heading_highlight,
                'body' => $intro?->body,
            ];
        }

        foreach (array_keys(self::IMAGE_ONLY) as $key) {
            $banner = SiteContent::section("{$key}.banner", onlyPublished: false);

            $data["{$key}_banner"] = [
                'existing_image' => $banner?->image,
                'image' => $this->uploadState($banner?->image),
            ];
        }

        $past = SiteContent::section('kegiatan.past', onlyPublished: false);
        $data['kegiatan_past'] = [
            'eyebrow' => $past?->eyebrow,
            'body' => $past?->body,
        ];

        $this->form->fill($data);
    }

    protected function persist(array $data): void
    {
        foreach (self::PAGES as $key => $label) {
            $banner = $data["{$key}_banner"] ?? [];
            SiteSection::query()->updateOrCreate(
                ['key' => "{$key}.banner"],
                [
                    'heading' => $banner['heading'] ?? null,
                    'image' => $this->normalizeUpload($banner['image'] ?? null) ?? ($banner['existing_image'] ?? null),
                    'is_published' => true,
                ],
            );

            $intro = $data["{$key}_intro"] ?? [];
            SiteSection::query()->updateOrCreate(
                ['key' => "{$key}.intro"],
                [
                    'eyebrow' => $intro['eyebrow'] ?? null,
                    'heading' => $intro['heading'] ?? null,
                    'heading_highlight' => $intro['heading_highlight'] ?? null,
                    'body' => $intro['body'] ?? null,
                    'is_published' => true,
                ],
            );
        }

        foreach (array_keys(self::IMAGE_ONLY) as $key) {
            $banner = $data["{$key}_banner"] ?? [];

            SiteSection::query()->updateOrCreate(
                ['key' => "{$key}.banner"],
                [
                    'image' => $this->normalizeUpload($banner['image'] ?? null) ?? ($banner['existing_image'] ?? null),
                    'is_published' => true,
                ],
            );
        }

        $past = $data['kegiatan_past'] ?? [];
        SiteSection::query()->updateOrCreate(
            ['key' => 'kegiatan.past'],
            [
                'eyebrow' => $past['eyebrow'] ?? null,
                'body' => $past['body'] ?? null,
                'is_published' => true,
            ],
        );

        SiteContent::forget();
    }

    private function uploadState(?string $path): mixed
    {
        if (! $path || str_starts_with($path, 'assets/')) {
            return null;
        }

        return $path;
    }

    private function normalizeUpload(mixed $value): ?string
    {
        if (is_array($value)) {
            return $value[0] ?? null;
        }

        return $value ?: null;
    }
}
