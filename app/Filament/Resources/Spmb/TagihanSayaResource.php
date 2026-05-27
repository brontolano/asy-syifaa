<?php

namespace App\Filament\Resources\Spmb;

use App\Models\Invoice;
use App\Models\PaymentProof;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TagihanSayaResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|\UnitEnum|null $navigationGroup = 'SPMB';
    protected static ?string $navigationLabel = 'Tagihan & Administrasi';
    protected static ?string $modelLabel = 'Tagihan';
    protected static ?string $pluralModelLabel = 'Tagihan & Administrasi';
    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasRole('Pendaftar');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth('erp')->user();
        $regIds = $user?->registrations()->pluck('id') ?? collect();

        return parent::getEloquentQuery()
            ->whereIn('ppdb_registration_id', $regIds);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No Tagihan')
                    ->searchable()
                    ->color(fn ($record) => $record->status === 'draft' ? 'gray' : null),
                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Rincian Biaya')
                    ->getStateUsing(function ($record) {
                        $items = $record->items()->with('billingType')->get();
                        return $items->map(fn ($i) => $i->description)->implode(', ');
                    })
                    ->limit(50)
                    ->color(fn ($record) => $record->status === 'draft' ? 'gray' : null)
                    ->tooltip(function ($record) {
                        $items = $record->items()->with('billingType')->get();
                        return $items->map(fn ($i) => $i->description . ': Rp ' . number_format($i->amount, 0, ',', '.'))->implode("\n");
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($record) => $record->status === 'draft' ? 'gray' : null),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Terbayar')
                    ->money('IDR')
                    ->default(0)
                    ->color(fn ($record) => $record->status === 'draft' ? 'gray' : null),
                Tables\Columns\TextColumn::make('remaining')
                    ->label('Sisa')
                    ->getStateUsing(fn ($record) => $record->total_amount - ($record->paid_amount ?? 0))
                    ->money('IDR')
                    ->color(fn ($record) => $record->status === 'draft' ? 'gray' : null),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'overdue' => 'danger',
                        'proof_submitted' => 'info',
                        'issued' => 'primary',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'paid' => 'Lunas',
                        'partial' => 'Cicilan',
                        'overdue' => 'Jatuh Tempo',
                        'proof_submitted' => 'Bukti Dikirim',
                        'issued' => 'Menunggu Pembayaran',
                        'draft' => 'Belum Aktif',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->color(fn ($record) => $record->status === 'draft' ? 'gray' : null),
            ])
            ->recordActions([
                Actions\Action::make('lihat_rincian')
                    ->label('Rincian')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Rincian Tagihan ' . $record->invoice_number)
                    ->modalContent(function ($record) {
                        $items = $record->items()->with('billingType')->get();
                        $html = '<div class="space-y-2">';
                        foreach ($items as $item) {
                            $amount = 'Rp ' . number_format($item->amount, 0, ',', '.');
                            $html .= "<div class=\"flex justify-between py-1 border-b border-gray-100\"><span class=\"text-sm\">{$item->description}</span><span class=\"text-sm font-semibold\">{$amount}</span></div>";
                        }
                        $total = 'Rp ' . number_format($record->total_amount, 0, ',', '.');
                        $html .= "<div class=\"flex justify-between pt-2 mt-2 border-t-2 border-gray-300\"><span class=\"font-bold\">TOTAL</span><span class=\"font-bold text-lg\">{$total}</span></div>";

                        if ($record->status === 'draft') {
                            $html .= '<div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg text-sm text-amber-700 dark:text-amber-300"><strong>Info:</strong> Tagihan ini belum aktif. Pembayaran dapat dilakukan setelah Anda dinyatakan diterima.</div>';
                        }

                        $html .= '</div>';
                        return new \Illuminate\Support\HtmlString($html);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Actions\Action::make('bayar_sekarang')
                    ->label('Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color(fn ($record) => self::canPay($record) ? 'success' : 'gray')
                    ->disabled(fn ($record) => !self::canPay($record))
                    ->tooltip(fn ($record) => match (true) {
                        $record->status === 'draft' => 'Tagihan belum aktif — menunggu keputusan seleksi',
                        $record->status === 'paid' => 'Sudah lunas',
                        default => 'Upload bukti pembayaran',
                    })
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah yang Dibayar (Rp)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->helperText('Anda bisa membayar sebagian (cicilan) atau penuh.'),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Foto Bukti Transfer')
                            ->directory('payment-proofs')
                            ->image()
                            ->maxSize(5120)
                            ->required()
                            ->helperText('Upload foto bukti transfer. Maks 5MB.'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Misal: Transfer via BCA, a/n ...')
                            ->maxLength(500),
                    ])
                    ->action(function ($record, array $data) {
                        PaymentProof::create([
                            'invoice_id' => $record->id,
                            'erp_account_id' => auth('erp')->id(),
                            'file_path' => $data['file_path'],
                            'notes' => ($data['notes'] ?? '') . ' | Nominal: Rp ' . number_format($data['amount'], 0, ',', '.'),
                            'status' => 'pending',
                        ]);

                        Notification::make()
                            ->title('Bukti pembayaran berhasil dikirim')
                            ->body('Staff akan mereview bukti transfer Anda.')
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('pay_online')
                    ->label('Online')
                    ->icon('heroicon-o-credit-card')
                    ->color('gray')
                    ->disabled()
                    ->tooltip('Segera hadir — Wave 2'),
            ])
            ->emptyStateHeading('Belum Ada Tagihan')
            ->emptyStateDescription('Tagihan belum tersedia.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    private static function canPay($record): bool
    {
        return in_array($record->status, ['issued', 'partial', 'overdue']);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Spmb\TagihanSayaResource\Pages\ListTagihanSaya::route('/'),
        ];
    }
}
