<?php

namespace App\Filament\Resources\Keuangan;

use App\Filament\Resources\Keuangan\InvoiceResource\Pages;
use App\Filament\Resources\Keuangan\InvoiceResource\RelationManagers;
use App\Models\BillingPeriod;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Mudir', 'Wakil Mudir', 'Bendahara', 'Kepala TU']);
    }

    protected static ?string $model = Invoice::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Tagihan';

    protected static ?string $modelLabel = 'Tagihan';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Data Tagihan')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('No. Tagihan')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        Forms\Components\TextInput::make('student_name')
                            ->label('Nama Santri')
                            ->required(),
                        Forms\Components\TextInput::make('student_id')
                            ->label('ID Santri')
                            ->helperText('Opsional, untuk referensi'),
                        Forms\Components\Select::make('billing_period_id')
                            ->label('Periode')
                            ->options(BillingPeriod::query()->pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'issued' => 'Terbit',
                                'partial' => 'Sebagian',
                                'paid' => 'Lunas',
                                'overdue' => 'Jatuh Tempo',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Jatuh Tempo'),
                        Forms\Components\DateTimePicker::make('issued_at')
                            ->label('Tgl Terbit'),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('Item Tagihan')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('billing_type_id')
                                    ->label('Jenis Biaya')
                                    ->relationship('billingType', 'name')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Set $set) {
                                        if ($state) {
                                            $billingType = \App\Models\BillingType::find($state);
                                            if ($billingType) {
                                                $set('description', $billingType->name);
                                                $set('amount', $billingType->amount_default);
                                            }
                                        }
                                    }),
                                Forms\Components\TextInput::make('description')
                                    ->label('Keterangan')
                                    ->required(),
                                Forms\Components\TextInput::make('amount')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Tagihan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student_name')
                    ->label('Nama Santri')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student_id')
                    ->label('NIS')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('invoice_type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => strtoupper($state))
                    ->color(fn (string $state) => match ($state) {
                        'spp' => 'primary',
                        'ujian' => 'info',
                        'muadalah' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('hijri_label')
                    ->label('Periode')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Terbayar')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'gray',
                        'issued' => 'info',
                        'partial' => 'warning',
                        'paid' => 'success',
                        'overdue', 'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'issued' => 'Terbit',
                        'partial' => 'Sebagian',
                        'paid' => 'Lunas',
                        'overdue' => 'Jatuh Tempo',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('billing_period_id')
                    ->label('Periode')
                    ->options(fn () => BillingPeriod::pluck('name', 'id')),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
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
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
