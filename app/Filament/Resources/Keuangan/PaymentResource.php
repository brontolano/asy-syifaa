<?php

namespace App\Filament\Resources\Keuangan;

use App\Filament\Resources\Keuangan\PaymentResource\Pages;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Mudir', 'Wakil Mudir', 'Bendahara', 'Kepala TU']);
    }

    protected static ?string $model = Payment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Pembayaran';

    protected static ?string $modelLabel = 'Pembayaran';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('invoice_id')
                    ->label('Tagihan')
                    ->options(fn () => Invoice::query()
                        ->whereNotIn('status', ['paid', 'cancelled'])
                        ->get()
                        ->mapWithKeys(fn ($inv) => [$inv->id => "{$inv->invoice_number} - {$inv->student_name}"]))
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Tanggal Bayar')
                    ->default(now())
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\Select::make('payment_method')
                    ->label('Metode')
                    ->options([
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer Bank',
                        'qris' => 'QRIS',
                    ])
                    ->default('cash')
                    ->required(),
                Forms\Components\TextInput::make('reference_number')
                    ->label('No. Referensi'),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('No. Tagihan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice.student_name')
                    ->label('Santri'),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Referensi'),
            ])
            ->defaultSort('payment_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode')
                    ->options([
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'qris' => 'QRIS',
                    ]),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
        ];
    }
}
