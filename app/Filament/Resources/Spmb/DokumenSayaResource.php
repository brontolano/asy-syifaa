<?php

namespace App\Filament\Resources\Spmb;

use App\Models\PpdbDocument;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DokumenSayaResource extends Resource
{
    protected static ?string $model = PpdbDocument::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static string|\UnitEnum|null $navigationGroup = 'SPMB';
    protected static ?string $navigationLabel = 'Dokumen Saya';
    protected static ?string $modelLabel = 'Dokumen';
    protected static ?string $pluralModelLabel = 'Dokumen Saya';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();

        return $user && $user->hasRole('Pendaftar');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth('erp')->user();
        $registrationIds = $user?->registrations()->pluck('id') ?? collect();

        return parent::getEloquentQuery()
            ->whereIn('ppdb_registration_id', $registrationIds);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options(config('spmb.mandatory_documents', []))
                    ->required()
                    ->disabled(fn (?PpdbDocument $record) => $record !== null),
                Forms\Components\FileUpload::make('file_path')
                    ->label('File Dokumen')
                    ->directory('ppdb-documents')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->maxSize(5120)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $documentLabels = config('spmb.mandatory_documents', []);

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration.student_name')
                    ->label('Nama Santri')
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Jenis Dokumen')
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
                    ->limit(50)
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('version')
                    ->label('Versi'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Upload')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->recordActions([
                Actions\Action::make('reupload')
                    ->label('Upload Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'rejected')
                    ->form([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('File Baru')
                            ->directory('ppdb-documents')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $registration = $record->registration;

                        PpdbDocument::create([
                            'ppdb_registration_id' => $registration->id,
                            'document_type' => $record->document_type,
                            'file_path' => $data['file_path'],
                            'status' => 'pending',
                            'version' => $record->version + 1,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Dokumen berhasil diupload ulang')
                            ->success()
                            ->send();
                    }),
                Actions\ViewAction::make(),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->label('Upload Dokumen')
                    ->mutateFormDataUsing(function (array $data): array {
                        $user = auth('erp')->user();
                        $data['ppdb_registration_id'] = $user->registration?->id;
                        $data['status'] = 'pending';
                        $data['version'] = 1;

                        return $data;
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Spmb\DokumenSayaResource\Pages\ListDokumenSaya::route('/'),
        ];
    }
}
