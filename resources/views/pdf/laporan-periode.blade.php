<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 11px; margin: 25px; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .right { text-align: right; }
    table { width: 100%; border-collapse: collapse; }
    .bordered td, .bordered th { border: 1px solid #333; padding: 5px 8px; }
    .bordered th { background: #e5e7eb; }
    .title { font-size: 16px; font-weight: bold; text-align: center; margin: 15px 0; }
    .kpi-box { display: inline-block; width: 23%; text-align: center; border: 1px solid #ccc; padding: 8px; margin: 3px; border-radius: 4px; }
    .kpi-label { font-size: 9px; color: #666; }
    .kpi-value { font-size: 16px; font-weight: bold; }
</style>
</head>
<body>
    @include('pdf.partials.header')

    <div class="title">LAPORAN KEUANGAN</div>
    <div class="center" style="margin-bottom: 15px;">
        Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->format('d F Y') }}
    </div>

    {{-- KPI Summary --}}
    <table style="margin-bottom: 15px;">
        <tr>
            <td class="center" style="border: 1px solid #ccc; padding: 10px; width: 25%;">
                <div class="kpi-label">TOTAL TRANSAKSI</div>
                <div class="kpi-value">{{ $totalTransaksi }}</div>
            </td>
            <td class="center" style="border: 1px solid #ccc; padding: 10px; width: 25%;">
                <div class="kpi-label">TOTAL PEMASUKAN</div>
                <div class="kpi-value">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
            </td>
            <td class="center" style="border: 1px solid #ccc; padding: 10px; width: 25%;">
                <div class="kpi-label">CASH</div>
                <div class="kpi-value">Rp {{ number_format($totalCash, 0, ',', '.') }}</div>
            </td>
            <td class="center" style="border: 1px solid #ccc; padding: 10px; width: 25%;">
                <div class="kpi-label">TRANSFER</div>
                <div class="kpi-value">Rp {{ number_format($totalTransfer, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    {{-- Breakdown by Channel --}}
    <h3>Breakdown Metode Pembayaran</h3>
    <table class="bordered" style="margin-bottom: 15px;">
        <thead>
            <tr><th>Channel</th><th>Jumlah Transaksi</th><th>Total</th></tr>
        </thead>
        <tbody>
            @foreach($byChannel as $channel => $data)
            <tr>
                <td>{{ ucfirst($channel ?: 'Lainnya') }}</td>
                <td class="center">{{ $data['count'] }}</td>
                <td class="right">Rp {{ number_format($data['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; font-size: 9px; color: #666; text-align: center;">
        Dicetak: {{ now()->format('d/m/Y H:i') }} | ERP Pesantren Asy-Syifaa
    </div>
</body>
</html>
