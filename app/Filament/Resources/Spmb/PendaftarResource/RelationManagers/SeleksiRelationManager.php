<?php

namespace App\Filament\Resources\Spmb\PendaftarResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class SeleksiRelationManager extends RelationManager
{
    protected static string $relationship = 'selectionStages';

    protected static ?string $title = 'Tahap Seleksi';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('stage_name')
                    ->label('Tahap')
                    ->options([
                        'admin_check' => 'Verifikasi Administrasi',
                        'interview' => 'Wawancara',
                        'quran_test' => 'Tes Baca Al-Quran',
                        'health_check' => 'Pemeriksaan Kesehatan',
                    ])
                    ->required(),
                Forms\Components\Select::make('result')
                    ->label('Hasil')
                    ->options([
                        'pending' => 'Menunggu',
                        'pass' => 'Lulus',
                        'fail' => 'Tidak Lulus',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\TextInput::make('score')
                    ->label('Nilai')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\DateTimePicker::make('conducted_at')
                    ->label('Tanggal Pelaksanaan'),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stage_name')
                    ->label('Tahap')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'admin_check' => 'Verifikasi Administrasi',
                        'interview' => 'Wawancara',
                        'quran_test' => 'Tes Baca Al-Quran',
                        'health_check' => 'Pemeriksaan Kesehatan',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('result')
                    ->label('Hasil')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'pass' => 'success',
                        'fail' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('score')
                    ->label('Nilai'),
                Tables\Columns\TextColumn::make('conducted_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                Actions\CreateAction::make()->label('Tambah Tahap'),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }
}
