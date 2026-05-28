<?php

namespace App\Filament\Resources\Notifikasi;

use App\Models\BroadcastJob;
use App\Models\NotificationTemplate;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BroadcastJobResource extends Resource
{
    protected static ?string $model = BroadcastJob::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
    protected static string|\UnitEnum|null $navigationGroup = 'Notifikasi';
    protected static ?string $navigationLabel = 'Broadcast';
    protected static ?string $modelLabel = 'Broadcast';
    protected static ?string $pluralModelLabel = 'Broadcast';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Broadcast')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Broadcast')
                        ->required()
                        ->helperText('Contoh: Pengumuman Libur Semester'),
                    Forms\Components\Select::make('template_id')
                        ->label('Template')
                        ->options(NotificationTemplate::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->helperText('Pilih template pesan yang sudah dibuat'),
                    Forms\Components\Select::make('channel')
                        ->label('Channel')
                        ->options([
                            'whatsapp' => 'WhatsApp',
                            'email' => 'Email',
                            'sms' => 'SMS',
                        ])
                        ->default('whatsapp')
                        ->required(),
                    Forms\Components\Select::make('filter_criteria.role')
                        ->label('Filter Role Penerima')
                        ->multiple()
                        ->options([
                            'Wali Santri' => 'Wali Santri',
                            'Santri' => 'Santri',
                            'Pendaftar' => 'Pendaftar',
                            'Guru' => 'Guru',
                            'Staf TU' => 'Staf TU',
                        ])
                        ->helperText('Kosongkan untuk kirim ke semua'),
                    Forms\Components\Hidden::make('status')->default('draft'),
                    Forms\Components\Hidden::make('created_by')
                        ->default(fn () => auth('erp')->id()),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('template.name')->label('Template')->placeholder('-'),
                Tables\Columns\TextColumn::make('channel')->label('Channel')->badge(),
                Tables\Columns\TextColumn::make('total_recipients')->label('Total'),
                Tables\Columns\TextColumn::make('sent_count')->label('Terkirim'),
                Tables\Columns\TextColumn::make('failed_count')->label('Gagal'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('creator.full_name')->label('Dibuat Oleh'),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Actions\EditAction::make(),
                Actions\Action::make('send')
                    ->label('Kirim')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (BroadcastJob $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Broadcast?')
                    ->modalDescription('Broadcast akan dikirim ke semua penerima yang memenuhi filter. Lanjutkan?')
                    ->action(function (BroadcastJob $record) {
                        $record->update([
                            'status' => 'processing',
                            'started_at' => now(),
                        ]);
                        // Dispatch to queue or process inline via N8nWebhookService
                        dispatch(function () use ($record) {
                            app(\App\Services\N8nWebhookService::class)->executeBroadcast($record);
                        })->afterResponse();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Notifikasi\BroadcastJobResource\Pages\ListBroadcastJobs::route('/'),
            'create' => \App\Filament\Resources\Notifikasi\BroadcastJobResource\Pages\CreateBroadcastJob::route('/create'),
            'edit' => \App\Filament\Resources\Notifikasi\BroadcastJobResource\Pages\EditBroadcastJob::route('/{record}/edit'),
        ];
    }
}
