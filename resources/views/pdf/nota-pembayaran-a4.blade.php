<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 12px; margin: 30px; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    table { width: 100%; border-collapse: collapse; }
    .bordered td, .bordered th { border: 1px solid #333; padding: 6px 8px; }
    .bordered th { background: #f3f4f6; text-align: left; }
    .right { text-align: right; }
    .title { font-size: 18px; font-weight: bold; text-align: center; margin: 20px 0 10px; }
    .info-table td { padding: 3px 8px; }
    .footer { margin-top: 40px; font-size: 10px; color: #666; }
</style>
</head>
<body>
    @include('pdf.partials.header')

    <div class="title">BUKTI PEMBAYARAN</div>
    <div class="center" style="margin-bottom: 20px; font-size: 11px;">No: {{ $payment->invoice?->invoice_number ?? '-' }}</div>

    <table class="info-table" style="margin-bottom: 15px;">
        <tr><td style="width: 120px;">Tanggal</td><td>: {{ $payment->payment_date?->format('d F Y') }}</td></tr>
        <tr><td>NIS</td><td>: {{ $payment->invoice?->student_id ?? '-' }}</td></tr>
        <tr><td>Nama Santri</td><td>: {{ $payment->invoice?->student_name ?? '-' }}</td></tr>
        <tr><td>Kelas</td><td>: {{ $payment->invoice?->student?->kelas_detail ?? '-' }}</td></tr>
        <tr><td>Jenjang</td><td>: {{ $payment->invoice?->student?->jenjang ?? '-' }}</td></tr>
    </table>

    <table class="bordered">
        <thead>
            <tr>
                <th>Keterangan</th>
                <th style="text-align: right;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ strtoupper($payment->invoice?->invoice_type ?? 'SPP') }} — {{ $payment->invoice?->hijri_label ?? '-' }}</td>
                <td class="right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="bold">
                <td>Total Dibayar</td>
                <td class="right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="info-table" style="margin-top: 10px;">
        <tr><td style="width: 120px;">Metode</td><td>: {{ ucfirst($payment->payment_channel ?? $payment->payment_method) }}</td></tr>
        @if($payment->reference_number)
        <tr><td>No. Referensi</td><td>: {{ $payment->reference_number }}</td></tr>
        @endif
        @if($payment->notes)
        <tr><td>Catatan</td><td>: {{ $payment->notes }}</td></tr>
        @endif
        <tr><td>Sisa Tagihan</td><td>: Rp {{ number_format($payment->invoice?->remaining ?? 0, 0, ',', '.') }}</td></tr>
    </table>

    <table style="margin-top: 50px;">
        <tr>
            <td style="width: 50%;"></td>
            <td class="center">
                <div>{{ $header?->address ? explode(',', $header->address)[0] ?? '' : '' }}, {{ now()->format('d F Y') }}</div>
                <div style="margin-top: 5px;">Bendahara</div>
                <div style="margin-top: 60px; border-top: 1px solid #333; display: inline-block; padding-top: 5px;">
                    {{ $payment->receiver?->full_name ?? '................................' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer center">Dicetak: {{ now()->format('d/m/Y H:i') }} | ERP Pesantren Asy-Syifaa</div>
</body>
</html>
