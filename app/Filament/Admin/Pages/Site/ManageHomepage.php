<?php

namespace App\Filament\Admin\Pages\Site;

use App\Filament\Admin\Pages\Concerns\ManagesSettingsForm;
use App\Models\Chrome\Partner;
use App\Models\Chrome\SiteSection;
use App\Models\Chrome\SiteSlide;
use App\Cms\Access\CmsAccess;
use App\Cms\Chrome\SiteContent;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManageHomepage extends Page
{
    use ManagesSettingsForm;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static ?string $navigationLabel = 'Beranda';

    protected static ?string $title = 'Kelola Beranda';

    protected static string|\UnitEnum|null $navigationGroup = 'Situs';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'beranda';

    public static function canAccess(): bool
    {
        return CmsAccess::can(Auth::user(), CmsAccess::HOMEPAGE);
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->fillForm();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Beranda')
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Hero')->schema([
                        Repeater::make('hero_slides')
                            ->label('Slide hero')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['highlight'] ?? $state['title'] ?? 'Slide')
                            ->schema($this->slideFields(withTags: true))
                            ->defaultItems(0),
                    ]),
                    Tab::make('Tentang & Statistik')->schema([
                        Section::make('Judul section')->schema([
                            TextInput::make('about.eyebrow')->label('Eyebrow'),
                            TextInput::make('about.heading')->label('Judul'),
                            TextInput::make('about.heading_highlight')->label('Bagian ditebalkan'),
                            TextInput::make('about.body')->label('Lanjutan judul'),
                            TextInput::make('about.cta_label')->label('Label tombol'),
                            TextInput::make('about.cta_url')->label('URL tombol'),
                        ])->columns(2),
                        Repeater::make('about.stats')
                            ->label('Kartu statistik')
                            ->reorderable()
                            ->schema([
                                TextInput::make('title')->label('Judul kartu')->required(),
                                TextInput::make('value')->label('Angka')->required(),
                                TextInput::make('suffix')->label('Sufiks')->placeholder('+'),
                                TextInput::make('label')->label('Keterangan')->required(),
                            ])
                            ->columns(4)
                            ->defaultItems(0),
                    ]),
                    Tab::make('Sorotan Riset')->schema([
                        Section::make('Judul section')->schema([
                            TextInput::make('feature.eyebrow')->label('Eyebrow'),
                            TextInput::make('feature.heading')->label('Judul'),
                            TextInput::make('feature.heading_highlight')->label('Bagian ditebalkan'),
                            TextInput::make('feature.body')->label('Lanjutan'),
                            TextInput::make('feature.cta_label')->label('Label tombol'),
                            TextInput::make('feature.cta_url')->label('URL tombol'),
                        ])->columns(2),
                        Repeater::make('feature_slides')
                            ->label('Slide sorotan')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Slide')
                            ->schema($this->slideFields())
                            ->defaultItems(0),
                    ]),
                    Tab::make('Kelompok Riset')->schema([
                        Section::make('Judul section')->schema([
                            TextInput::make('clusters.eyebrow')->label('Eyebrow'),
                            TextInput::make('clusters.heading')->label('Judul'),
                            TextInput::make('clusters.heading_highlight')->label('Bagian ditebalkan'),
                        ])->columns(3),
                        Repeater::make('cluster_slides')
                            ->label('Kluster')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Kluster')
                            ->schema([
                                Hidden::make('existing_image'),
                                TextInput::make('title')->label('Nama')->required(),
                                Textarea::make('body')->label('Deskripsi')->rows(3)->required(),
                                FileUpload::make('image')->label('Gambar')->image()->directory('site/clusters'),
                                Toggle::make('is_published')->label('Tayang')->default(true),
                            ])
                            ->defaultItems(0),
                    ]),
                    Tab::make('Banner IMAGy')->schema([
                        TextInput::make('imagy.eyebrow')->label('Eyebrow kiri'),
                        TextInput::make('imagy.body')->label('Eyebrow kanan / label'),
                        TextInput::make('imagy.heading')->label('Judul utama'),
                        TextInput::make('imagy.heading_highlight')->label('Subjudul'),
                        TextInput::make('imagy.cta_url')->label('URL ikon'),
                        TextInput::make('imagy.tags')->label('Tag (pisahkan koma)'),
                    ])->columns(2),
                    Tab::make('Label Section')->schema([
                        Section::make('Kegiatan')->schema($this->sectionChromeFields('events'))->columns(2)->compact(),
                        Section::make('Publikasi')->schema($this->sectionChromeFields('publications'))->columns(2)->compact(),
                        Section::make('Berita')->schema($this->sectionChromeFields('news'))->columns(2)->compact(),
                        Section::make('Dokumentasi')->schema($this->sectionChromeFields('gallery'))->columns(2)->compact(),
                        Section::make('Kerja Sama')->schema([
                            TextInput::make('partners.eyebrow')->label('Eyebrow'),
                            TextInput::make('partners.heading')->label('Judul'),
                            TextInput::make('partners.heading_highlight')->label('Bagian ditebalkan'),
                            TextInput::make('partners.body')->label('Subjudul mitra'),
                        ])->columns(2)->compact(),
                    ]),
                    Tab::make('Mitra')->schema([
                        Repeater::make('partners_list')
                            ->label('Logo mitra')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Mitra')
                            ->schema([
                                Hidden::make('existing_logo'),
                                TextInput::make('name')->label('Nama')->required(),
                                FileUpload::make('logo')->label('Logo')->image()->directory('site/partners'),
                                TextInput::make('url')->label('URL')->url(),
                                Toggle::make('is_published')->label('Tayang')->default(true),
                            ])
                            ->columns(2)
                            ->defaultItems(0),
                    ]),
                ]),
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    private function slideFields(bool $withTags = false): array
    {
        $fields = [
            Hidden::make('existing_image'),
            TextInput::make('highlight')->label('Highlight / span'),
            TextInput::make('title')->label('Judul')->required(),
            Textarea::make('body')->label('Teks')->rows(3),
            FileUpload::make('image')->label('Gambar')->image()->directory('site/slides'),
            TextInput::make('link_url')->label('URL tautan'),
            TextInput::make('link_label')->label('Label tautan'),
            Toggle::make('is_published')->label('Tayang')->default(true),
        ];

        if ($withTags) {
            array_splice($fields, 4, 0, [
                TextInput::make('tags')->label('Tag (pisahkan koma)'),
            ]);
        }

        return $fields;
    }

    /**
     * @return array<int, mixed>
     */
    private function sectionChromeFields(string $prefix): array
    {
        return [
            TextInput::make("{$prefix}.eyebrow")->label('Eyebrow'),
            TextInput::make("{$prefix}.heading")->label('Judul'),
            TextInput::make("{$prefix}.heading_highlight")->label('Bagian ditebalkan'),
            TextInput::make("{$prefix}.cta_label")->label('Label tombol'),
            TextInput::make("{$prefix}.cta_url")->label('URL tombol'),
        ];
    }

    protected function fillForm(): void
    {
        $about = SiteContent::section('home.about', onlyPublished: false);
        $feature = SiteContent::section('home.feature', onlyPublished: false);
        $clusters = SiteContent::section('home.clusters', onlyPublished: false);
        $imagy = SiteContent::section('home.imagy', onlyPublished: false);

        $this->form->fill([
            'hero_slides' => $this->mapSlides(SiteSlide::PLACEMENT_HERO),
            'feature_slides' => $this->mapSlides(SiteSlide::PLACEMENT_FEATURE),
            'cluster_slides' => $this->mapSlides(SiteSlide::PLACEMENT_CLUSTER),
            'about' => $this->mapSection($about) + [
                'stats' => $about?->meta('stats', []),
            ],
            'feature' => $this->mapSection($feature),
            'clusters' => $this->mapSection($clusters),
            'imagy' => $this->mapSection($imagy) + [
                'tags' => implode(', ', $imagy?->meta('tags', []) ?? []),
            ],
            'events' => $this->mapSection(SiteContent::section('home.events', onlyPublished: false)),
            'publications' => $this->mapSection(SiteContent::section('home.publications', onlyPublished: false)),
            'news' => $this->mapSection(SiteContent::section('home.news', onlyPublished: false)),
            'gallery' => $this->mapSection(SiteContent::section('home.gallery', onlyPublished: false)),
            'partners' => $this->mapSection(SiteContent::section('home.partners', onlyPublished: false)),
            'partners_list' => Partner::query()->orderBy('sort_order')->get()->map(fn (Partner $p) => [
                'name' => $p->name,
                'existing_logo' => $p->logo,
                'logo' => $this->uploadState($p->logo),
                'url' => $p->url,
                'is_published' => $p->is_published,
            ])->all(),
        ]);
    }

    protected function persist(array $data): void
    {
        DB::transaction(function () use ($data) {
            $this->syncSection('home.about', $data['about'] ?? [], [
                'stats' => $data['about']['stats'] ?? [],
            ]);
            $this->syncSection('home.feature', $data['feature'] ?? []);
            $this->syncSection('home.clusters', $data['clusters'] ?? []);
            $this->syncSection('home.events', $data['events'] ?? []);
            $this->syncSection('home.publications', $data['publications'] ?? []);
            $this->syncSection('home.news', $data['news'] ?? []);
            $this->syncSection('home.gallery', $data['gallery'] ?? []);
            $this->syncSection('home.partners', $data['partners'] ?? []);

            $imagy = $data['imagy'] ?? [];
            $tags = collect(explode(',', (string) ($imagy['tags'] ?? '')))
                ->map(fn ($t) => trim($t))
                ->filter()
                ->values()
                ->all();
            unset($imagy['tags']);
            $this->syncSection('home.imagy', $imagy, ['tags' => $tags]);

            $this->syncSlides(SiteSlide::PLACEMENT_HERO, $data['hero_slides'] ?? []);
            $this->syncSlides(SiteSlide::PLACEMENT_FEATURE, $data['feature_slides'] ?? []);
            $this->syncSlides(SiteSlide::PLACEMENT_CLUSTER, $data['cluster_slides'] ?? []);
            $this->syncPartners($data['partners_list'] ?? []);
        });

        SiteContent::forget();
    }

    /**
     * @param  array<string, mixed>  $fields
     * @param  array<string, mixed>  $meta
     */
    private function syncSection(string $key, array $fields, array $meta = []): void
    {
        SiteSection::query()->updateOrCreate(
            ['key' => $key],
            [
                'eyebrow' => $fields['eyebrow'] ?? null,
                'heading' => $fields['heading'] ?? null,
                'heading_highlight' => $fields['heading_highlight'] ?? null,
                'body' => $fields['body'] ?? null,
                'cta_label' => $fields['cta_label'] ?? null,
                'cta_url' => $fields['cta_url'] ?? null,
                'meta' => $meta ?: ($fields['stats'] ?? null ? ['stats' => $fields['stats']] : null),
                'is_published' => true,
            ],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $slides
     */
    private function syncSlides(string $placement, array $slides): void
    {
        SiteSlide::query()->where('placement', $placement)->delete();

        foreach (array_values($slides) as $index => $slide) {
            $tags = $slide['tags'] ?? null;
            if (is_string($tags)) {
                $tags = collect(explode(',', $tags))->map(fn ($t) => trim($t))->filter()->values()->all();
            }

            SiteSlide::query()->create([
                'placement' => $placement,
                'title' => $slide['title'] ?? null,
                'highlight' => $slide['highlight'] ?? null,
                'body' => $slide['body'] ?? null,
                'tags' => $tags,
                'image' => $this->normalizeUpload($slide['image'] ?? null) ?? ($slide['existing_image'] ?? null),
                'link_url' => $slide['link_url'] ?? null,
                'link_label' => $slide['link_label'] ?? null,
                'sort_order' => $index + 1,
                'is_published' => (bool) ($slide['is_published'] ?? true),
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $partners
     */
    private function syncPartners(array $partners): void
    {
        Partner::query()->delete();

        foreach (array_values($partners) as $index => $partner) {
            Partner::query()->create([
                'name' => $partner['name'],
                'logo' => $this->normalizeUpload($partner['logo'] ?? null) ?? ($partner['existing_logo'] ?? null),
                'url' => $partner['url'] ?? null,
                'sort_order' => $index + 1,
                'is_published' => (bool) ($partner['is_published'] ?? true),
            ]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapSlides(string $placement): array
    {
        return SiteSlide::query()
            ->placement($placement)
            ->get()
            ->map(fn (SiteSlide $slide) => [
                'title' => $slide->title,
                'highlight' => $slide->highlight,
                'body' => $slide->body,
                'tags' => is_array($slide->tags) ? implode(', ', $slide->tags) : null,
                'existing_image' => $slide->image,
                'image' => $this->uploadState($slide->image),
                'link_url' => $slide->link_url,
                'link_label' => $slide->link_label,
                'is_published' => $slide->is_published,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapSection(?SiteSection $section): array
    {
        if ($section === null) {
            return [];
        }

        return [
            'eyebrow' => $section->eyebrow,
            'heading' => $section->heading,
            'heading_highlight' => $section->heading_highlight,
            'body' => $section->body,
            'cta_label' => $section->cta_label,
            'cta_url' => $section->cta_url,
        ];
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
