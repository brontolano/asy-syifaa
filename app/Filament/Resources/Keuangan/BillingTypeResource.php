<?php

namespace App\Filament\Resources\Keuangan;

use App\Filament\Resources\Keuangan\BillingTypeResource\Pages;
use App\Models\BillingType;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class BillingTypeResource extends Resource
{
    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Bendahara']);
    }

    protected static ?string $model = BillingType::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Jenis Biaya';

    protected static ?string $modelLabel = 'Jenis Biaya';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Biaya')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Keterangan')
                    ->rows(2),
                Forms\Components\TextInput::make('amount_default')
                    ->label('Nominal Default')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\Toggle::make('is_recurring')
                    ->label('Berulang (per bulan)')
                    ->default(false),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('amount_default')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_recurring')->label('Berulang')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBillingTypes::route('/'),
            'create' => Pages\CreateBillingType::route('/create'),
            'edit' => Pages\EditBillingType::route('/{record}/edit'),
        ];
    }
}
