<?php

namespace App\Filament\Admin\Support;

use App\Cms\Access\CmsAccess;
use App\Cms\Media\ImageOptimizer;
use App\Models\Auth\User;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class ContentFormFields
{
    /**
     * Rich text dengan sisipan gambar opsional (disk publik + kompresi).
     * Sampul/hero tetap kolom FileUpload terpisah.
     */
    public static function richBody(string $name, string $label, string $directory): RichEditor
    {
        $folder = trim($directory, '/');

        return RichEditor::make($name)
            ->label($label)
            ->helperText('Gambar di dalam teks bersifat opsional. Sisipkan lewat ikon lampiran di bilah alat editor.')
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsDirectory($folder)
            ->fileAttachmentsVisibility('public')
            ->fileAttachmentsAcceptedFileTypes([
                'image/png',
                'image/jpeg',
                'image/webp',
                'image/gif',
            ])
            ->fileAttachmentsMaxSize(5120)
            ->saveUploadedFileAttachmentUsing(function (TemporaryUploadedFile $file) use ($folder): string {
                $user = Auth::user() instanceof User ? Auth::user() : null;

                return ImageOptimizer::store($file, $folder, $user);
            })
            ->columnSpanFull();
    }

    public static function workflow(bool $featured = true): Section
    {
        $fields = [
            Select::make('workflow_status')
                ->label('Alur terbit')
                ->options(fn () => CmsAccess::workflowOptions(auth()->user()))
                ->default(fn () => CmsAccess::canPublish(auth()->user())
                    ? CmsAccess::WORKFLOW_PUBLISHED
                    : CmsAccess::WORKFLOW_DRAFT)
                ->required()
                ->helperText('Editor hanya sampai tahap ditinjau. Tayang dilakukan admin konten atau super admin.'),
        ];

        if ($featured) {
            $fields[] = Toggle::make('is_featured')->label('Sorot di beranda');
        }

        return Section::make('Alur terbit')
            ->schema($fields)
            ->columns(2);
    }

    public static function seo(): Section
    {
        return Section::make('SEO')
            ->collapsed()
            ->schema([
                TextInput::make('seo_title')
                    ->label('Judul SEO')
                    ->maxLength(70)
                    ->columnSpanFull(),
                Textarea::make('seo_description')
                    ->label('Deskripsi SEO')
                    ->rows(2)
                    ->maxLength(160)
                    ->columnSpanFull(),
            ]);
    }
}
