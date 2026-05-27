<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 8px; margin: 10px; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    table { width: 100%; border-collapse: collapse; }
    td, th { border: 1px solid #999; padding: 3px 4px; }
    th { background: #e5e7eb; font-size: 7px; }
    .title { font-size: 13px; font-weight: bold; text-align: center; margin: 8px 0; }
    .paid { background: #d1fae5; color: #065f46; font-weight: bold; }
    .partial { background: #fef3c7; color: #92400e; }
    .overdue, .issued { background: #fee2e2; color: #991b1b; }
    .none { background: #f9fafb; color: #ccc; }
    .legend { font-size: 8px; margin-top: 8px; }
    .legend span { display: inline-block; padding: 2px 6px; margin-right: 5px; border: 1px solid #ccc; }
</style>
</head>
<body>
    @include('pdf.partials.header')

    <div class="title">MATRIX SYAHRIYYAH {{ $hijriYear }} H</div>
    <div class="center" style="margin-bottom: 6px;">
        @if($kelas) Kelas: {{ $kelas }} | @endif
        Dicetak: {{ now()->format('d/m/Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 55px;">NIS</th>
                <th style="min-width: 120px;">Nama</th>
                <th style="width: 35px;">Kelas</th>
                @foreach($periods as $p)
                <th style="width: 40px;">{{ substr($p->hijri_month_name, 0, 3) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $i => $row)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $row['student']->nis }}</td>
                <td>{{ $row['student']->full_name }}</td>
                <td class="center">{{ $row['student']->kelas }}</td>
                @foreach($periods as $p)
                @php $st = $row['months'][$p->id] ?? 'none'; @endphp
                <td class="center {{ $st }}">
                    @if($st === 'paid') &#10003;
                    @elseif($st === 'partial') ~
                    @elseif($st === 'none') —
                    @else &#10007;
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="legend">
        Keterangan:
        <span class="paid">&#10003; Lunas</span>
        <span class="partial">~ Cicilan</span>
        <span class="issued">&#10007; Belum Bayar</span>
        <span class="none">— Belum Ada Tagihan</span>
    </div>
</body>
</html>
