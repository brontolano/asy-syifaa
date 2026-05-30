<?php

namespace App\Filament\Resources\Notifikasi;

use App\Models\NotificationTemplate;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string|\UnitEnum|null $navigationGroup = 'Notifikasi';
    protected static ?string $navigationLabel = 'Template Pesan';
    protected static ?string $modelLabel = 'Template Notifikasi';
    protected static ?string $pluralModelLabel = 'Template Notifikasi';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Template')
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Nama Template')->required(),
                    Forms\Components\TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
                    Forms\Components\Select::make('channel')
                        ->label('Channel')
                        ->options([
                            'whatsapp' => 'WhatsApp',
                            'email' => 'Email',
                            'sms' => 'SMS',
                        ])
                        ->default('whatsapp')
                        ->required(),
                    Forms\Components\TextInput::make('subject')
                        ->label('Subject (untuk email)')
                        ->helperText('Kosongkan untuk WhatsApp/SMS'),
                    Forms\Components\Textarea::make('body_template')
                        ->label('Isi Template')
                        ->required()
                        ->rows(8)
                        ->helperText('Gunakan {variable} untuk placeholder. Contoh: Halo {name}, pendaftaran Anda #{registration_number} telah diterima.')
                        ->columnSpanFull(),
                    Forms\Components\TagsInput::make('variables')
                        ->label('Variabel yang Tersedia')
                        ->helperText('Daftar variabel yang bisa dipakai di template')
                        ->placeholder('Tambah variabel...')
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug'),
                Tables\Columns\TextColumn::make('channel')->label('Channel')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'whatsapp' => 'success',
                        'email' => 'info',
                        'sms' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('logs_count')->label('Terkirim')->counts('logs'),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Notifikasi\NotificationTemplateResource\Pages\ListNotificationTemplates::route('/'),
            'create' => \App\Filament\Resources\Notifikasi\NotificationTemplateResource\Pages\CreateNotificationTemplate::route('/create'),
            'edit' => \App\Filament\Resources\Notifikasi\NotificationTemplateResource\Pages\EditNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
