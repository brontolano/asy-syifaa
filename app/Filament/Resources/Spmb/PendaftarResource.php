<?php

namespace App\Filament\Resources\Spmb;

use App\Filament\Resources\Spmb\PendaftarResource\Pages;
use App\Filament\Resources\Spmb\PendaftarResource\RelationManagers;
use App\Models\PpdbRegistration;
use App\Services\SpmbService;
use App\Services\WebhookNotificationService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class PendaftarResource extends Resource
{
    public static function canAccess(): bool
    {
        $user = auth('erp')->user();

        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Mudir', 'Wakil Mudir', 'Kepala TU', 'Staf TU']);
    }

    protected static ?string $model = PpdbRegistration::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'SPMB';

    protected static ?string $navigationLabel = 'Data Pendaftar';

    protected static ?string $modelLabel = 'Pendaftar';

    protected static ?string $pluralModelLabel = 'Data Pendaftar';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Tabs::make('Data Pendaftar')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('Data Pribadi')
                            ->schema([
                                Forms\Components\TextInput::make('registration_number')
                                    ->label('No. Pendaftaran')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visibleOn('edit'),
                                Forms\Components\TextInput::make('academic_year')
                                    ->label('Tahun Ajaran')
                                    ->required()
                                    ->default(date('Y') . '/' . (date('Y') + 1))
                                    ->maxLength(9),
                                Forms\Components\TextInput::make('student_name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('nik')
                                    ->label('NIK')
                                    ->maxLength(16),
                                Forms\Components\DatePicker::make('birth_date')
                                    ->label('Tanggal Lahir'),
                                Forms\Components\TextInput::make('birth_place')
                                    ->label('Tempat Lahir'),
                                Forms\Components\Select::make('gender')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'L' => 'Laki-laki',
                                        'P' => 'Perempuan',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('origin_school')
                                    ->label('Asal Sekolah'),
                                Forms\Components\Textarea::make('address')
                                    ->label('Alamat')
                                    ->rows(3),
                            ])
                            ->columns(2),

                        \Filament\Schemas\Components\Tabs\Tab::make('Data Orang Tua')
                            ->schema([
                                Forms\Components\TextInput::make('parent_name')
                                    ->label('Nama Orang Tua/Wali')
                                    ->required(),
                                Forms\Components\TextInput::make('parent_phone')
                                    ->label('No. HP Orang Tua')
                                    ->tel()
                                    ->required(),
                                Forms\Components\TextInput::make('parent_email')
                                    ->label('Email Orang Tua')
                                    ->email(),
                            ])
                            ->columns(2),

                        \Filament\Schemas\Components\Tabs\Tab::make('Status')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Menunggu',
                                        'document_review' => 'Review Dokumen',
                                        'selection' => 'Seleksi',
                                        'lulus' => 'Lulus',
                                        'cadangan' => 'Cadangan',
                                        'rejected' => 'Tidak Lulus',
                                        'enrolled' => 'Santri Aktif',
                                    ])
                                    ->default('pending')
                                    ->required(),
                                Forms\Components\Select::make('source')
                                    ->label('Sumber')
                                    ->options([
                                        'website' => 'Website',
                                        'manual' => 'Manual',
                                    ])
                                    ->default('manual')
                                    ->disabled(),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Catatan')
                                    ->rows(3),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')
                    ->label('No. Pendaftaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('JK')
                    ->formatStateUsing(fn (string $state) => $state === 'L' ? 'Laki-laki' : 'Perempuan'),
                Tables\Columns\TextColumn::make('parent_phone')
                    ->label('No. HP Ortu'),
                Tables\Columns\TextColumn::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'document_review' => 'info',
                        'selection' => 'primary',
                        'lulus', 'enrolled' => 'success',
                        'cadangan' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Menunggu',
                        'document_review' => 'Review Dokumen',
                        'selection' => 'Seleksi',
                        'lulus' => 'Lulus',
                        'cadangan' => 'Cadangan',
                        'rejected' => 'Tidak Lulus',
                        'enrolled' => 'Santri Aktif',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('document_status')
                    ->label('Dokumen')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'complete' => 'success',
                        'revision_needed' => 'danger',
                        'pending_review' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'complete' => 'Lengkap',
                        'revision_needed' => 'Revisi',
                        'pending_review' => 'Review',
                        'incomplete' => 'Belum Lengkap',
                        default => $state ?? '-',
                    }),
                Tables\Columns\TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Daftar')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'document_review' => 'Review Dokumen',
                        'selection' => 'Seleksi',
                        'lulus' => 'Lulus',
                        'cadangan' => 'Cadangan',
                        'rejected' => 'Tidak Lulus',
                        'enrolled' => 'Santri Aktif',
                    ]),
                Tables\Filters\SelectFilter::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->options(fn () => PpdbRegistration::query()
                        ->distinct()
                        ->pluck('academic_year', 'academic_year')
                        ->toArray()),
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'website' => 'Website',
                        'manual' => 'Manual',
                    ]),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\Action::make('terima')
                    ->label('Lulus')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PpdbRegistration $record) => ! in_array($record->status, ['lulus', 'enrolled', 'rejected']))
                    ->action(function (PpdbRegistration $record) {
                        app(SpmbService::class)->setSelectionResult($record, 'lulus');
                        Notification::make()->title('Calon santri dinyatakan lulus')->success()->send();
                    }),
                Actions\Action::make('cadangan')
                    ->label('Cadangan')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (PpdbRegistration $record) => ! in_array($record->status, ['lulus', 'enrolled', 'rejected']))
                    ->action(function (PpdbRegistration $record) {
                        app(SpmbService::class)->setSelectionResult($record, 'cadangan');
                        Notification::make()->title('Calon santri masuk cadangan')->warning()->send();
                    }),
                Actions\Action::make('tolak')
                    ->label('Tidak Lulus')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (PpdbRegistration $record) => ! in_array($record->status, ['lulus', 'enrolled', 'rejected']))
                    ->action(function (PpdbRegistration $record) {
                        app(SpmbService::class)->setSelectionResult($record, 'rejected');
                        Notification::make()->title('Calon santri tidak lulus')->danger()->send();
                    }),
                Actions\Action::make('kirim_credential')
                    ->label('Kirim Credential WA')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Credential via WhatsApp')
                    ->modalDescription('Password baru akan digenerate dan dikirim via WhatsApp ke nomor orang tua.')
                    ->action(function (PpdbRegistration $record) {
                        $plainPassword = app(SpmbService::class)->resetAndSendCredential($record);
                        $account = $record->fresh()->account;

                        if ($account) {
                            app(WebhookNotificationService::class)->sendCredential(
                                $record,
                                $account->username,
                                $plainPassword,
                            );
                        }

                        Notification::make()
                            ->title('Credential berhasil dikirim via WhatsApp')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DokumenRelationManager::class,
            RelationManagers\SeleksiRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendaftar::route('/'),
            'create' => Pages\CreatePendaftar::route('/create'),
            'view' => Pages\ViewPendaftar::route('/{record}'),
            'edit' => Pages\EditPendaftar::route('/{record}/edit'),
        ];
    }
}
