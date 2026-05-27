<?php

namespace App\Filament\Resources\Spmb\PendaftarResource\RelationManagers;

use App\Services\SpmbService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class DokumenRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Dokumen';

    public function form(Schema $schema): Schema
    {
        $documentOptions = config('spmb.mandatory_documents', []);

        return $schema
            ->schema([
                Forms\Components\Select::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options($documentOptions)
                    ->required(),
                Forms\Components\FileUpload::make('file_path')
                    ->label('File')
                    ->directory('ppdb-documents')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->maxSize(5120)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        $documentLabels = config('spmb.mandatory_documents', []);

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Jenis')
                    ->formatStateUsing(fn (string $state) => $documentLabels[$state] ?? $state),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => 'Menunggu Review',
                    }),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('Alasan Tolak')
                    ->limit(40)
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('version')
                    ->label('Versi'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Upload')
                    ->dateTime('d/m/Y'),
            ])
            ->headerActions([
                Actions\CreateAction::make()->label('Upload Dokumen'),
            ])
            ->recordActions([
                Actions\Action::make('preview')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => asset('storage/' . $record->file_path), shouldOpenInNewTab: true),
                Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Dokumen')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui dokumen ini?')
                    ->visible(fn ($record) => $record->status !== 'approved')
                    ->action(function ($record) {
                        app(SpmbService::class)->verifyDocument(
                            $record,
                            auth('erp')->user(),
                            'approved',
                        );

                        Notification::make()
                            ->title('Dokumen disetujui')
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->visible(fn ($record) => $record->status !== 'rejected')
                    ->action(function ($record, array $data) {
                        app(SpmbService::class)->verifyDocument(
                            $record,
                            auth('erp')->user(),
                            'rejected',
                            $data['rejection_reason'],
                        );

                        Notification::make()
                            ->title('Dokumen ditolak')
                            ->warning()
                            ->send();
                    }),
                Actions\DeleteAction::make(),
            ]);
    }
}
