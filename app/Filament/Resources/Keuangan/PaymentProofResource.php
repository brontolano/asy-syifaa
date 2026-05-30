<?php

namespace App\Filament\Resources\Keuangan;

use App\Events\PaymentProofApproved;
use App\Models\PaymentProof;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PaymentProofResource extends Resource
{
    protected static ?string $model = PaymentProof::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';
    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Verifikasi Transfer';
    protected static ?string $modelLabel = 'Bukti Transfer';
    protected static ?string $pluralModelLabel = 'Bukti Transfer';
    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Mudir', 'Wakil Mudir', 'Bendahara', 'Kepala TU']);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Placeholder::make('info')
                ->label('')
                ->content('Bukti transfer hanya bisa diverifikasi (setujui / tolak) lewat aksi di tabel.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Bukti')
                    ->disk('public')
                    ->height(48),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'topup' => 'Setor Tabungan',
                        default => 'Tagihan',
                    })
                    ->color(fn (?string $state) => $state === 'topup' ? 'info' : 'gray'),
                Tables\Columns\TextColumn::make('student.full_name')
                    ->label('Santri')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('No. Tagihan')
                    ->searchable()
                    ->placeholder('— (setoran)'),
                Tables\Columns\TextColumn::make('nominal_transfer')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_transfer')
                    ->label('Tgl Transfer')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('bank_pengirim')
                    ->label('Bank/Pengirim')
                    ->description(fn (PaymentProof $r) => $r->nama_pengirim)
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default    => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dikirim')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending'),
            ])
            ->recordActions([
                Actions\Action::make('lihat')
                    ->label('Lihat Bukti')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (PaymentProof $r) => $r->file_path ? Storage::disk('public')->url($r->file_path) : null)
                    ->openUrlInNewTab(),
                Actions\Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PaymentProof $r) => $r->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Bukti Transfer')
                    ->modalDescription(fn (PaymentProof $r) => $r->type === 'topup'
                        ? 'Saldo tabungan santri akan ditambahkan sesuai nominal setoran. Lanjutkan?'
                        : 'Pembayaran akan dicatat & sisa tagihan dilunasi. Lanjutkan?')
                    ->action(function (PaymentProof $record) {
                        $record->update([
                            'status'      => 'approved',
                            'reviewed_by' => auth('erp')->id(),
                            'reviewed_at' => now(),
                        ]);
                        event(new PaymentProofApproved($record));

                        \Filament\Notifications\Notification::make()
                            ->title('Bukti transfer disetujui')
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PaymentProof $r) => $r->status === 'pending')
                    ->schema([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (PaymentProof $record, array $data) {
                        $record->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'reviewed_by'      => auth('erp')->id(),
                            'reviewed_at'      => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Bukti transfer ditolak')
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Keuangan\PaymentProofResource\Pages\ListPaymentProofs::route('/'),
        ];
    }
}
