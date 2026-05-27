<?php

namespace App\Filament\Pages;

use App\Models\ErpAccount;
use App\Models\PpdbRegistration;
use App\Notifications\PengumumanUmum;
use App\Services\WebhookNotificationService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class BroadcastNotifikasi extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $title = 'Broadcast Notifikasi';
    protected static string|\UnitEnum|null $navigationGroup = 'SPMB';
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.broadcast-notifikasi';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();

        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Mudir', 'Wakil Mudir', 'Kepala TU', 'Staf TU']);
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('judul')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('pesan')
                    ->label('Isi Pesan')
                    ->required(),
                Forms\Components\Select::make('target')
                    ->label('Target')
                    ->options([
                        'all' => 'Semua Calon Santri',
                        'selected' => 'Pilih Manual',
                    ])
                    ->default('all')
                    ->required()
                    ->live(),
                Forms\Components\Select::make('selected_registrations')
                    ->label('Pilih Calon Santri')
                    ->multiple()
                    ->searchable()
                    ->options(fn () => PpdbRegistration::query()
                        ->whereNotNull('erp_account_id')
                        ->pluck('student_name', 'id')
                        ->toArray())
                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('target') === 'selected'),
                Forms\Components\CheckboxList::make('channels')
                    ->label('Kirim Via')
                    ->options([
                        'push' => 'Push Notification (In-App)',
                        'whatsapp' => 'WhatsApp',
                    ])
                    ->default(['push'])
                    ->required(),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $plainMessage = strip_tags($data['pesan']);

        // Get target registrations
        if ($data['target'] === 'all') {
            $registrations = PpdbRegistration::whereNotNull('erp_account_id')
                ->with('account')
                ->get();
        } else {
            $registrations = PpdbRegistration::whereIn('id', $data['selected_registrations'] ?? [])
                ->whereNotNull('erp_account_id')
                ->with('account')
                ->get();
        }

        $channels = $data['channels'];
        $webhook = app(WebhookNotificationService::class);
        $sent = 0;

        foreach ($registrations as $registration) {
            // Push notification (in-app)
            if (in_array('push', $channels) && $registration->account) {
                $registration->account->notify(new PengumumanUmum($data['judul'], $plainMessage));
            }

            // WhatsApp
            if (in_array('whatsapp', $channels) && $registration->parent_phone) {
                $webhook->sendBroadcast($registration->parent_phone, "{$data['judul']}\n\n{$plainMessage}");
            }

            $sent++;
        }

        Notification::make()
            ->title("Broadcast berhasil dikirim ke {$sent} calon santri")
            ->success()
            ->send();

        $this->form->fill();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('send')
                ->label('Kirim Broadcast')
                ->icon('heroicon-o-paper-airplane')
                ->submit('send'),
        ];
    }
}
