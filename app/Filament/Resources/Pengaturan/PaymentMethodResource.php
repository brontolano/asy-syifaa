<?php

namespace App\Filament\Resources\Pengaturan;

use App\Models\PaymentMethod;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Metode Pembayaran';
    protected static ?string $modelLabel = 'Metode Pembayaran';
    protected static ?string $pluralModelLabel = 'Metode Pembayaran';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Bendahara']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Informasi Metode Pembayaran')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Kode')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(30)
                        ->helperText('Contoh: cash, transfer_bsi, transfer_bca, qris'),
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Tampilan')
                        ->required()
                        ->helperText('Contoh: Tunai, Transfer BSI, Transfer BCA'),
                    Forms\Components\TextInput::make('bank_name')
                        ->label('Nama Bank')
                        ->helperText('Kosongkan jika cash/tunai'),
                    Forms\Components\TextInput::make('account_number')
                        ->label('Nomor Rekening')
                        ->helperText('Kosongkan jika cash/tunai'),
                    Forms\Components\TextInput::make('account_holder')
                        ->label('Atas Nama'),
                    Forms\Components\TextInput::make('icon')
                        ->label('Icon')
                        ->default('heroicon-o-banknotes')
                        ->helperText('Heroicon name'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Urutan')
                        ->numeric()
                        ->default(0),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('bank_name')->label('Bank')->placeholder('-'),
                Tables\Columns\TextColumn::make('account_number')->label('No. Rekening')->placeholder('-'),
                Tables\Columns\TextColumn::make('account_holder')->label('Atas Nama')->placeholder('-'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
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
            'index' => \App\Filament\Resources\Pengaturan\PaymentMethodResource\Pages\ListPaymentMethods::route('/'),
            'create' => \App\Filament\Resources\Pengaturan\PaymentMethodResource\Pages\CreatePaymentMethod::route('/create'),
            'edit' => \App\Filament\Resources\Pengaturan\PaymentMethodResource\Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
