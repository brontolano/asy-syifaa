<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class ChangePassword extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';
    protected static ?string $title = 'Ubah Password';
    protected static ?string $slug = 'change-password';
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.change-password';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('new_password')
                    ->label('Password Baru')
                    ->password()
                    ->required()
                    ->rule(Password::min(8))
                    ->revealable(),
                TextInput::make('new_password_confirmation')
                    ->label('Konfirmasi Password Baru')
                    ->password()
                    ->required()
                    ->same('new_password')
                    ->revealable(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = auth('erp')->user();
        $user->update([
            'password' => $data['new_password'],
            'must_change_password' => false,
        ]);

        Notification::make()
            ->title('Password berhasil diubah')
            ->success()
            ->send();

        $this->redirect('/');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Password')
                ->submit('save'),
        ];
    }
}
