<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 10px; margin: 20px; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .right { text-align: right; }
    table { width: 100%; border-collapse: collapse; }
    .bordered td, .bordered th { border: 1px solid #333; padding: 4px 6px; }
    .bordered th { background: #e5e7eb; text-align: center; font-size: 9px; }
    .title { font-size: 14px; font-weight: bold; text-align: center; margin: 10px 0; }
    .danger { color: #dc2626; font-weight: bold; }
</style>
</head>
<body>
    @include('pdf.partials.header')

    <div class="title">DAFTAR TUNGGAKAN SANTRI</div>
    <div class="center" style="margin-bottom: 10px;">
        @if($kelas) Kelas: {{ $kelas }} @endif
        @if($jenjang) | Jenjang: {{ $jenjang }} @endif
        | Tanggal Cetak: {{ $tanggal }}
    </div>

    <table class="bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama Santri</th>
                <th>Kelas</th>
                <th>Jenjang</th>
                <th>Tunggakan (Bln)</th>
                <th>Nominal Tunggakan</th>
                <th>No. HP Wali</th>
                <th>Nama Wali</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $i => $s)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $s->nis }}</td>
                <td>{{ $s->full_name }}</td>
                <td class="center">{{ $s->kelas_detail ?? $s->kelas }}</td>
                <td class="center">{{ $s->jenjang }}</td>
                <td class="center {{ $s->tunggakan_bulan >= 3 ? 'danger' : '' }}">{{ $s->tunggakan_bulan }}</td>
                <td class="right danger">Rp {{ number_format($s->tunggakan_nominal, 0, ',', '.') }}</td>
                <td>{{ $s->phone ?? '-' }}</td>
                <td>{{ $s->wali_nama_display }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bold" style="background: #fee2e2;">
                <td colspan="5" class="center">TOTAL ({{ $students->count() }} santri)</td>
                <td class="center">{{ $students->sum('tunggakan_bulan') }} bln</td>
                <td class="right">Rp {{ number_format($students->sum('tunggakan_nominal'), 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <table style="margin-top: 30px;">
        <tr>
            <td>
                <div>Mengetahui,</div>
                <div>Mudir</div>
                <div style="margin-top: 50px; border-top: 1px solid #333; display: inline-block; padding: 3px 30px;">
                    ................................
                </div>
            </td>
            <td class="right">
                <div>{{ $tanggal }}</div>
                <div>Bendahara</div>
                <div style="margin-top: 50px; border-top: 1px solid #333; display: inline-block; padding: 3px 30px;">
                    ................................
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
