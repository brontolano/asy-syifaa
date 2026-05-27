<?php

namespace App\Filament\Resources\Kepesantrenan;

use App\Models\Student;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AlumniResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|\UnitEnum|null $navigationGroup = 'Kepesantrenan';
    protected static ?string $navigationLabel = 'Alumni & Lainnya';
    protected static ?string $modelLabel = 'Alumni';
    protected static ?string $pluralModelLabel = 'Alumni & Santri Non-Aktif';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'kepesantrenan/alumni';

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Mudir', 'Wakil Mudir', 'Kepala TU', 'Staf TU', 'Kepala Akademik']);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->whereIn('status', ['alumni', 'waqof', 'pengabdian', 'tendik', 'mutasi', 'dikeluarkan']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nis')->label('NIS')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('full_name')->label('Nama')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('kelas_detail')->label('Kelas Terakhir'),
                Tables\Columns\TextColumn::make('jenjang')->label('Jenjang')->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Wustha' => 'info', 'Ulya' => 'success', 'Tamhidi' => 'warning', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'alumni' => 'gray', 'waqof' => 'danger', 'pengabdian' => 'info',
                        'tendik' => 'primary', 'mutasi' => 'warning', 'dikeluarkan' => 'danger', default => 'gray',
                    })->formatStateUsing(fn (string $state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('tahun_masuk')->label('Thn Masuk')->sortable(),
                Tables\Columns\TextColumn::make('tahun_keluar')->label('Thn Keluar')->sortable(),
                Tables\Columns\TextColumn::make('gender')->label('L/P')
                    ->formatStateUsing(fn (string $state) => $state === 'L' ? 'L' : 'P'),
            ])
            ->defaultSort('nis')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'alumni' => 'Alumni',
                        'waqof' => 'Waqof',
                        'pengabdian' => 'Pengabdian',
                        'tendik' => 'Tendik',
                        'mutasi' => 'Mutasi',
                        'dikeluarkan' => 'Dikeluarkan',
                    ]),
                Tables\Filters\SelectFilter::make('jenjang')
                    ->options(['Wustha' => 'Wustha', 'Ulya' => 'Ulya', 'Tamhidi' => 'Tamhidi', 'Takhassus' => 'Takhassus']),
            ])
            ->searchable()
            ->recordActions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        // Reuse StudentResource form
        return StudentResource::form($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Kepesantrenan\AlumniResource\Pages\ListAlumni::route('/'),
        ];
    }
}
