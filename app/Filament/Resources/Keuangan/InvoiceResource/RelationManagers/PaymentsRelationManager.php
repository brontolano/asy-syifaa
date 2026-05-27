<?php

namespace App\Filament\Resources\Keuangan\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Riwayat Pembayaran';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'qris' => 'QRIS',
                        default => $state,
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Referensi'),
                Tables\Columns\IconColumn::make('verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->verified_at !== null),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->label('Catat Pembayaran')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['received_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }
}
