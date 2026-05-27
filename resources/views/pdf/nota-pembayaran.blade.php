<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 11px; margin: 5px; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .line { border-top: 1px dashed #333; margin: 5px 0; }
    table { width: 100%; border-collapse: collapse; }
    td { padding: 2px 0; }
    .right { text-align: right; }
</style>
</head>
<body>
    <div class="center bold" style="font-size: 13px;">
        {{ $header?->institution_name ?? 'Pondok Pesantren Asy-Syifaa' }}
    </div>
    @if($header?->address)<div class="center" style="font-size: 8px;">{{ $header->address }}</div>@endif
    <div class="line"></div>
    <div class="center bold">NOTA PEMBAYARAN</div>
    <div class="center" style="font-size: 9px;">No: {{ $payment->invoice?->invoice_number ?? '-' }}</div>
    <div class="line"></div>

    <table>
        <tr><td>Tanggal</td><td class="right">{{ $payment->payment_date?->format('d/m/Y H:i') }}</td></tr>
        <tr><td>NIS</td><td class="right">{{ $payment->invoice?->student_id ?? '-' }}</td></tr>
        <tr><td>Nama</td><td class="right">{{ $payment->invoice?->student_name ?? '-' }}</td></tr>
        <tr><td>Kelas</td><td class="right">{{ $payment->invoice?->student?->kelas_detail ?? '-' }}</td></tr>
    </table>

    <div class="line"></div>
    <table>
        <tr><td>Jenis</td><td class="right">{{ strtoupper($payment->invoice?->invoice_type ?? 'SPP') }}</td></tr>
        <tr><td>Periode</td><td class="right">{{ $payment->invoice?->hijri_label ?? '-' }}</td></tr>
        <tr class="bold"><td>Jumlah Bayar</td><td class="right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td></tr>
        <tr><td>Metode</td><td class="right">{{ ucfirst($payment->payment_channel ?? $payment->payment_method) }}</td></tr>
        @if($payment->reference_number)
        <tr><td>Referensi</td><td class="right">{{ $payment->reference_number }}</td></tr>
        @endif
    </table>

    <div class="line"></div>
    <table>
        <tr><td>Total Tagihan</td><td class="right">Rp {{ number_format($payment->invoice?->total_amount ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td>Total Terbayar</td><td class="right">Rp {{ number_format($payment->invoice?->paid_amount ?? 0, 0, ',', '.') }}</td></tr>
        <tr class="bold"><td>Sisa</td><td class="right">Rp {{ number_format($payment->invoice?->remaining ?? 0, 0, ',', '.') }}</td></tr>
    </table>

    <div class="line"></div>
    <div class="center" style="font-size: 9px; margin-top: 5px;">
        Diterima oleh: {{ $payment->receiver?->full_name ?? '-' }}<br>
        Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>
    <div class="center" style="font-size: 8px; margin-top: 8px;">Terima kasih atas pembayarannya</div>
</body>
</html>
