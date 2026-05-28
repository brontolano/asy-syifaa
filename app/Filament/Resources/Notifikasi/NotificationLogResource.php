<?php

namespace App\Filament\Resources\Notifikasi;

use App\Models\NotificationLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationLogResource extends Resource
{
    protected static ?string $model = NotificationLog::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static string|\UnitEnum|null $navigationGroup = 'Notifikasi';
    protected static ?string $navigationLabel = 'Log Notifikasi';
    protected static ?string $modelLabel = 'Log Notifikasi';
    protected static ?string $pluralModelLabel = 'Log Notifikasi';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('channel')->label('Channel')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'whatsapp' => 'success',
                        'email' => 'info',
                        'sms' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('recipient')->label('Penerima')->searchable(),
                Tables\Columns\TextColumn::make('subject')->label('Subject')->limit(40),
                Tables\Columns\TextColumn::make('template.name')->label('Template')->placeholder('-'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'sent' => 'success',
                        'delivered' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('sent_at')->label('Terkirim')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('error')->label('Error')->limit(30)->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('channel')
                    ->options(['whatsapp' => 'WhatsApp', 'email' => 'Email', 'sms' => 'SMS']),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'sent' => 'Sent', 'failed' => 'Failed', 'delivered' => 'Delivered']),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Notifikasi\NotificationLogResource\Pages\ListNotificationLogs::route('/'),
        ];
    }
}
