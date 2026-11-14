<?php

namespace App\Filament\Admin\Pages\Site;

use App\Cms\Access\CmsAccess;
use App\Filament\Admin\Pages\Concerns\ManagesSettingsForm;
use App\Models\Chrome\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ManageSiteIdentity extends Page
{
    use ManagesSettingsForm;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'Identitas & Kontak';

    protected static ?string $title = 'Identitas & Kontak';

    protected static string|\UnitEnum|null $navigationGroup = 'Situs';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'identitas-situs';

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
            Section::make('Identitas')
                ->schema([
                    TextInput::make('site_name')->label('Nama situs')->required(),
                    TextInput::make('site_tagline')->label('Tagline')->required(),
                    FileUpload::make('logo_light')
                        ->label('Logo terang (hero gelap)')
                        ->image()
                        ->directory('brand')
                        ->helperText('Kosongkan untuk mempertahankan logo SVG bawaan.'),
                    FileUpload::make('logo_dark')
                        ->label('Logo gelap (menu putih)')
                        ->image()
                        ->directory('brand'),
                    FileUpload::make('favicon')
                        ->label('Favicon (ikon tab browser)')
                        ->image()
                        ->directory('brand')
                        ->helperText('PNG/SVG persegi. Kosongkan untuk memakai ikon bawaan.'),
                    FileUpload::make('preloader_logo')
                        ->label('Logo preloader')
                        ->image()
                        ->directory('brand')
                        ->helperText('Tampil saat halaman memuat. Kosongkan untuk logo bawaan.'),
                ])
                ->columns(2),
            Section::make('Kontak')
                ->schema([
                    TextInput::make('email')->label('Email')->email()->required(),
                    TextInput::make('phone')->label('Telepon'),
                    Textarea::make('address')->label('Alamat')->rows(3)->columnSpanFull(),
                    Textarea::make('map_embed')->label('Embed peta (iframe Google Maps)')->rows(3)->columnSpanFull()
                        ->helperText('Tempel kode iframe dari Google Maps → Bagikan → Sematkan peta.'),
                ])
                ->columns(2),
            Section::make('Media sosial')
                ->schema([
                    TextInput::make('whatsapp')->label('WhatsApp')->url(),
                    TextInput::make('instagram')->label('Instagram')->url(),
                    TextInput::make('youtube')->label('YouTube')->url(),
                    TextInput::make('facebook')->label('Facebook')->url(),
                ])
                ->columns(2),
            Section::make('SEO dasar')
                ->schema([
                    Textarea::make('meta_description')->label('Meta description')->rows(3),
                    TextInput::make('analytics_id')->label('Google Analytics / GTM ID')
                        ->helperText('Contoh G-XXXX atau GTM-XXXX. Kosongkan untuk mematikan. Bisa juga lewat ANALYTICS_ID di .env.'),
                ]),
        ]);
    }

    protected function fillForm(): void
    {
        $keys = [
            'site_name', 'site_tagline', 'logo_light', 'logo_dark',
            'favicon', 'preloader_logo',
            'email', 'phone', 'address', 'map_embed',
            'whatsapp', 'instagram', 'youtube', 'facebook',
            'meta_description', 'analytics_id',
        ];

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = Setting::get($key);
        }

        // Jangan tampilkan path aset bawaan sebagai FileUpload state.
        foreach (['logo_light', 'logo_dark', 'favicon', 'preloader_logo'] as $logoKey) {
            if (is_string($data[$logoKey] ?? null) && str_starts_with($data[$logoKey], 'assets/')) {
                $data[$logoKey] = null;
            }
        }

        $this->form->fill($data);
    }

    protected function persist(array $data): void
    {
        $groups = [
            'site_name' => 'general',
            'site_tagline' => 'general',
            'logo_light' => 'brand',
            'logo_dark' => 'brand',
            'favicon' => 'brand',
            'preloader_logo' => 'brand',
            'email' => 'contact',
            'phone' => 'contact',
            'address' => 'contact',
            'map_embed' => 'contact',
            'whatsapp' => 'social',
            'instagram' => 'social',
            'youtube' => 'social',
            'facebook' => 'social',
            'meta_description' => 'seo',
            'analytics_id' => 'seo',
        ];

        foreach ($groups as $key => $group) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];

            if (in_array($key, ['logo_light', 'logo_dark', 'favicon', 'preloader_logo'], true) && blank($value)) {
                continue;
            }

            Setting::set($key, is_array($value) ? ($value[0] ?? null) : $value, $group);
        }
    }
}
