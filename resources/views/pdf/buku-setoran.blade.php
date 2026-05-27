<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 10px; margin: 15px; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .right { text-align: right; }
    table { width: 100%; border-collapse: collapse; }
    .bordered td, .bordered th { border: 1px solid #333; padding: 4px 5px; }
    .bordered th { background: #e5e7eb; text-align: center; font-size: 9px; }
    .title { font-size: 14px; font-weight: bold; text-align: center; margin: 10px 0; }
    .summary { background: #f3f4f6; padding: 8px; border-radius: 4px; margin-bottom: 10px; }
</style>
</head>
<body>
    @include('pdf.partials.header')

    <div class="title">BUKU SETORAN HARIAN</div>
    <div class="center" style="margin-bottom: 10px;">
        Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
    </div>

    <div class="summary">
        <table>
            <tr>
                <td>Total Transaksi: <strong>{{ $payments->count() }}</strong></td>
                <td>Total Pemasukan: <strong>Rp {{ number_format($payments->sum('amount'), 0, ',', '.') }}</strong></td>
                <td>Cash: <strong>Rp {{ number_format($totalCash, 0, ',', '.') }}</strong></td>
                <td>Transfer: <strong>Rp {{ number_format($totalTransfer, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <table class="bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>NIS</th>
                <th>Nama Santri</th>
                <th>Kelas</th>
                <th>No Invoice</th>
                <th>Jenis</th>
                <th>Metode</th>
                <th>Channel</th>
                <th>Jumlah</th>
                <th>Referensi</th>
                <th>Penerima</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $i => $p)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $p->payment_date?->format('d/m/Y') }}</td>
                <td>{{ $p->invoice?->student_id ?? '-' }}</td>
                <td>{{ $p->invoice?->student_name ?? '-' }}</td>
                <td class="center">{{ $p->invoice?->student?->kelas_detail ?? '-' }}</td>
                <td style="font-size: 8px;">{{ $p->invoice?->invoice_number ?? '-' }}</td>
                <td class="center">{{ strtoupper($p->invoice?->invoice_type ?? '-') }}</td>
                <td class="center">{{ ucfirst($p->payment_method) }}</td>
                <td class="center">{{ $p->payment_channel ?? '-' }}</td>
                <td class="right bold">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                <td style="font-size: 8px;">{{ $p->reference_number ?? '-' }}</td>
                <td>{{ $p->receiver?->full_name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bold" style="background: #e5e7eb;">
                <td colspan="9" class="center">TOTAL</td>
                <td class="right">Rp {{ number_format($payments->sum('amount'), 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <table style="margin-top: 30px;">
        <tr>
            <td>Mengetahui, Kepala TU<br><br><br><br>................................</td>
            <td class="center">Diperiksa, Mudir<br><br><br><br>................................</td>
            <td class="right">Dibuat, Bendahara<br><br><br><br>................................</td>
        </tr>
    </table>
</body>
</html>
