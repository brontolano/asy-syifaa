<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 11px; margin: 25px; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    table { width: 100%; border-collapse: collapse; }
    .bordered td, .bordered th { border: 1px solid #333; padding: 5px 8px; font-size: 10px; }
    .bordered th { background: #e5e7eb; text-align: center; }
    .title { font-size: 16px; font-weight: bold; text-align: center; margin: 15px 0; }
    .paid { background: #d1fae5; }
    .partial { background: #fef3c7; }
    .unpaid { background: #fee2e2; }
    .none { background: #f9fafb; }
    .info td { padding: 2px 6px; }
</style>
</head>
<body>
    @include('pdf.partials.header')

    <div class="title">BUKU PEMBAYARAN SPP (SYAHRIYYAH)</div>
    <div class="center" style="margin-bottom: 15px;">Tahun Hijriah: {{ $hijriYear }} H</div>

    <table class="info" style="margin-bottom: 15px;">
        <tr><td style="width: 100px;">NIS</td><td>: {{ $student->nis }}</td></tr>
        <tr><td>Nama</td><td>: {{ $student->full_name }}</td></tr>
        <tr><td>Kelas</td><td>: {{ $student->kelas_detail ?? $student->kelas }}</td></tr>
        <tr><td>Jenjang</td><td>: {{ $student->jenjang }}</td></tr>
        <tr><td>Nominal SPP</td><td>: Rp {{ number_format($student->spp_amount, 0, ',', '.') }}/bulan</td></tr>
    </table>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Bulan Hijriah</th>
                <th>Tagihan</th>
                <th>Terbayar</th>
                <th>Sisa</th>
                <th>Status</th>
                <th>Tgl Bayar</th>
                <th>Paraf</th>
            </tr>
        </thead>
        <tbody>
            @php $totalTagihan = 0; $totalBayar = 0; @endphp
            @foreach($periods as $i => $period)
            @php
                $inv = $invoices[$period->id] ?? null;
                $status = $inv ? $inv->status : 'none';
                $class = match($status) {
                    'paid' => 'paid',
                    'partial' => 'partial',
                    'none' => 'none',
                    default => 'unpaid',
                };
                $totalTagihan += $inv ? $inv->total_amount : 0;
                $totalBayar += $inv ? $inv->paid_amount : 0;
                $lastPayment = $inv?->payments?->last();
            @endphp
            <tr class="{{ $class }}">
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $period->label }}</td>
                <td class="center">{{ $inv ? 'Rp '.number_format($inv->total_amount, 0, ',', '.') : '-' }}</td>
                <td class="center">{{ $inv ? 'Rp '.number_format($inv->paid_amount, 0, ',', '.') : '-' }}</td>
                <td class="center">{{ $inv ? 'Rp '.number_format($inv->remaining, 0, ',', '.') : '-' }}</td>
                <td class="center bold">
                    @if($status === 'paid') LUNAS
                    @elseif($status === 'partial') CICILAN
                    @elseif($status === 'none') —
                    @else BELUM
                    @endif
                </td>
                <td class="center">{{ $lastPayment?->payment_date?->format('d/m/Y') ?? '-' }}</td>
                <td style="width: 50px;"></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bold" style="background: #e5e7eb;">
                <td colspan="2" class="center">TOTAL</td>
                <td class="center">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                <td class="center">Rp {{ number_format($totalBayar, 0, ',', '.') }}</td>
                <td class="center">Rp {{ number_format($totalTagihan - $totalBayar, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px; font-size: 9px; color: #666;">
        Dicetak: {{ now()->format('d/m/Y H:i') }} | ERP Pesantren Asy-Syifaa
    </div>
</body>
</html>
