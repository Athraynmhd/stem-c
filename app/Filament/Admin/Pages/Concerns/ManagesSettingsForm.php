<?php

namespace App\Filament\Admin\Pages\Concerns;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Throwable;

trait ManagesSettingsForm
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    abstract protected function fillForm(): void;

    abstract protected function persist(array $data): void;

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $this->persist($data);
        } catch (Halt $exception) {
            return;
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            throw $exception;
        }

        Notification::make()
            ->title('Perubahan disimpan')
            ->success()
            ->send();

        // Isi ulang form dari DB agar FilePond tidak macet di "Memuat"
        // setelah berkas temp Livewire diganti path permanen.
        $this->fillForm();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->operation('edit')
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Simpan perubahan')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ])->key('form-actions'),
                    ]),
            ]);
    }
}
