<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentProof;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentPermission;
use App\Models\TahfidzProgress;
use App\Models\TahfidzRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * API untuk Asy-Syifaa App (PWA Wali Santri)
 * Semua endpoint memerlukan autentikasi Sanctum token dengan role wali_santri.
 */
class WaliController extends Controller
{
    /**
     * GET /api/v1/wali/santri
     * Data santri milik wali yang login.
     */
    public function santri(Request $request): JsonResponse
    {
        $wali = $request->user();
        $students = Student::where('wali_account_id', $wali->id)
            ->select([
                'id', 'nis', 'full_name', 'kelas', 'kelas_detail',
                'jenjang', 'rombel', 'status', 'tahun_masuk',
                'gender', 'birth_date', 'jalur_masuk',
                'tunggakan_bulan', 'spp_amount',
            ])
            ->get()
            ->map(function (Student $s) {
                return [
                    'id'            => $s->id,
                    'nis'           => $s->nis,
                    'nama'          => $s->full_name,
                    'kelas'         => trim(($s->kelas ?? '') . ' ' . ($s->kelas_detail ?? '')),
                    'jenjang'       => $s->jenjang,
                    'rombel'        => $s->rombel,
                    'status'        => $s->status,
                    'tahun_masuk'   => $s->tahun_masuk,
                    'gender'        => $s->gender,
                    'tanggal_lahir' => $s->birth_date?->format('Y-m-d'),
                    'jalur_masuk'   => $s->jalur_masuk,
                    'tunggakan_bulan' => $s->tunggakan_bulan,
                    'spp_amount'    => $s->spp_amount,
                ];
            });

        return response()->json([
            'ok'   => true,
            'data' => $students,
        ]);
    }

    /**
     * GET /api/v1/wali/santri/{studentId}/status-harian
     * Status kehadiran & kesehatan hari ini + 7 hari terakhir.
     */
    public function statusHarian(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $hari_ini = StudentAttendance::where('student_id', $studentId)
            ->whereDate('tanggal', today())
            ->get()
            ->map(fn($a) => $this->formatAbsensi($a));

        $riwayat = StudentAttendance::where('student_id', $studentId)
            ->whereDate('tanggal', '>=', now()->subDays(30))
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn($a) => $this->formatAbsensi($a));

        $rekap_bulan = StudentAttendance::where('student_id', $studentId)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->selectRaw("status_kehadiran, COUNT(*) as total")
            ->groupBy('status_kehadiran')
            ->pluck('total', 'status_kehadiran');

        return response()->json([
            'ok'         => true,
            'hari_ini'   => $hari_ini,
            'riwayat'    => $riwayat,
            'rekap_bulan'=> [
                'hadir' => $rekap_bulan['hadir'] ?? 0,
                'sakit' => $rekap_bulan['sakit'] ?? 0,
                'izin'  => $rekap_bulan['izin']  ?? 0,
                'alfa'  => $rekap_bulan['alfa']  ?? 0,
            ],
        ]);
    }

    /**
     * GET /api/v1/wali/santri/{studentId}/hafalan
     * Progress & riwayat setoran hafalan.
     */
    public function hafalan(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $progress = TahfidzProgress::where('student_id', $studentId)->first();

        $riwayat = TahfidzRecord::where('student_id', $studentId)
            ->with('ustadz:id,full_name')
            ->orderByDesc('tanggal_setor')
            ->limit(20)
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'tanggal'      => $r->tanggal_setor->format('Y-m-d'),
                'kategori'     => $r->kategori,
                'pencapaian'   => $r->pencapaian,
                'jenis'        => $r->jenis_setor,
                'jenis_label'  => $r->jenis_label,
                'nilai'        => $r->nilai,
                'nilai_label'  => $r->nilai_label,
                'catatan'      => $r->catatan_ustadz,
                'ustadz'       => $r->ustadz?->full_name,
            ]);

        return response()->json([
            'ok'       => true,
            'progress' => $progress ? [
                'total_juz_quran'   => $progress->total_juz_quran,
                'target_juz_quran'  => $progress->target_juz_quran,
                'persen_quran'      => $progress->persen_quran,
                'total_hadist'      => $progress->total_hadist,
                'target_hadist'     => $progress->target_hadist,
                'update_terakhir'   => $progress->update_terakhir?->format('Y-m-d'),
                'catatan'           => $progress->catatan,
            ] : null,
            'riwayat_setor' => $riwayat,
        ]);
    }

    /**
     * GET /api/v1/wali/santri/{studentId}/tagihan
     * Daftar tagihan & status pembayaran.
     */
    public function tagihan(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $tagihan = Invoice::where('student_id_fk', $studentId)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->with('items:id,invoice_id,description,amount')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($inv) => [
                'id'             => $inv->id,
                'nomor'          => $inv->invoice_number,
                'jenis'          => $inv->invoice_type,
                'label_periode'  => $inv->hijri_label,
                'total'          => (float) $inv->total_amount,
                'terbayar'       => (float) $inv->paid_amount,
                'sisa'           => (float) ($inv->total_amount - $inv->paid_amount),
                'status'         => $inv->status,
                'jatuh_tempo'    => $inv->due_date?->format('Y-m-d'),
                'items'          => $inv->items->map(fn($i) => [
                    'keterangan' => $i->description,
                    'nominal'    => (float) $i->amount,
                ]),
            ]);

        $total_tunggakan = $tagihan
            ->whereIn('status', ['issued', 'partial', 'overdue'])
            ->sum('sisa');

        return response()->json([
            'ok'              => true,
            'total_tunggakan' => $total_tunggakan,
            'data'            => $tagihan->values(),
        ]);
    }

    /**
     * POST /api/v1/wali/santri/{studentId}/tagihan/{invoiceId}/bukti-bayar
     * Upload bukti transfer dari wali.
     */
    public function uploadBuktiBayar(Request $request, int $studentId, int $invoiceId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $validated = $request->validate([
            'foto'             => 'required|image|max:5120',
            'nominal_transfer' => 'required|numeric|min:1000',
            'tanggal_transfer' => 'required|date',
            'bank_pengirim'    => 'nullable|string|max:50',
            'nama_pengirim'    => 'nullable|string|max:100',
            'catatan'          => 'nullable|string|max:500',
        ]);

        $invoice = Invoice::where('id', $invoiceId)
            ->where('student_id_fk', $studentId)
            ->firstOrFail();

        $path = $request->file('foto')->store('payment-proofs', 'public');

        $proof = PaymentProof::create([
            'invoice_id'       => $invoice->id,
            'student_id'       => $studentId,
            'erp_account_id'   => $request->user()->id,
            'file_path'        => $path,
            'nominal_transfer' => $validated['nominal_transfer'],
            'tanggal_transfer' => $validated['tanggal_transfer'],
            'bank_pengirim'    => $request->bank_pengirim,
            'nama_pengirim'    => $request->nama_pengirim,
            'notes'            => $request->catatan,
            'status'           => 'pending',
        ]);

        // Update invoice status ke partial jika masih issued
        if ($invoice->status === 'issued') {
            $invoice->update(['status' => 'partial']);
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Bukti transfer berhasil dikirim. Menunggu konfirmasi admin.',
            'data'    => ['id' => $proof->id, 'status' => $proof->status],
        ], 201);
    }

    /**
     * GET /api/v1/wali/santri/{studentId}/payment-methods
     * Daftar metode pembayaran aktif (VA bank, e-wallet, QRIS).
     */
    public function paymentMethods(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $methods = \App\Models\PaymentMethod::active()
            ->get()
            ->map(fn($m) => [
                'id'             => $m->id,
                'code'           => $m->code,
                'type'           => $m->type ?? 'bank',
                'name'           => $m->name,
                'bank_name'      => $m->bank_name,
                'account_number' => $m->account_number,
                'account_holder' => $m->account_holder,
                'icon'           => $m->icon,
                'qris_image_url' => $m->qris_image_path
                    ? Storage::disk('public')->url($m->qris_image_path)
                    : null,
                'instructions'   => $m->instructions,
            ]);

        return response()->json([
            'ok'   => true,
            'data' => $methods->values(),
        ]);
    }

    /**
     * GET /api/v1/wali/santri/{studentId}/izin
     * Riwayat & status izin santri.
     */
    public function daftarIzin(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $izin = StudentPermission::where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($i) => $this->formatIzin($i));

        return response()->json([
            'ok'   => true,
            'data' => $izin,
        ]);
    }

    /**
     * POST /api/v1/wali/santri/{studentId}/izin
     * Ajukan izin baru.
     */
    public function ajukanIzin(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $request->validate([
            'jenis_izin'     => 'required|in:pulang,keluar_area,kegiatan_luar,sakit,lainnya',
            'tanggal_mulai'  => 'required|date|after_or_equal:today',
            'tanggal_selesai'=> 'nullable|date|after_or_equal:tanggal_mulai',
            'jam_keluar'     => 'nullable|date_format:H:i',
            'jam_kembali'    => 'nullable|date_format:H:i',
            'alasan'         => 'required|string|min:10|max:1000',
            'tujuan'         => 'nullable|string|max:200',
        ]);

        $izin = StudentPermission::create([
            'student_id'       => $studentId,
            'pengaju_wali_id'  => $request->user()->id,
            'jenis_izin'       => $request->jenis_izin,
            'tanggal_mulai'    => $request->tanggal_mulai,
            'tanggal_selesai'  => $request->tanggal_selesai,
            'jam_keluar'       => $request->jam_keluar,
            'jam_kembali'      => $request->jam_kembali,
            'alasan'           => $request->alasan,
            'tujuan'           => $request->tujuan,
            'status'           => 'menunggu',
        ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Permohonan izin berhasil dikirim. Menunggu persetujuan.',
            'data'    => $this->formatIzin($izin),
        ], 201);
    }

    // ─── Keuangan ─────────────────────────────────────────────

    /**
     * GET /api/v1/wali/santri/{studentId}/tabungan
     * Saldo, limit jajan, dan status tabungan.
     */
    public function tabungan(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $savings = DB::table('student_savings')->where('student_id', $studentId)->first();

        if (!$savings) {
            return response()->json([
                'ok'   => true,
                'data' => [
                    'saldo'              => 0,
                    'limit_harian'       => 30000,
                    'is_frozen'          => false,
                    'saldo_dapat_dipakai'=> 0,
                    'transaksi_hari_ini' => 0,
                ],
            ]);
        }

        $transaksi_hari_ini = DB::table('savings_transactions')
            ->where('student_id', $studentId)
            ->where('jenis', 'debit')
            ->whereDate('created_at', today())
            ->sum('nominal');

        return response()->json([
            'ok'   => true,
            'data' => [
                'saldo'               => (float) $savings->saldo,
                'limit_harian'        => (float) $savings->limit_harian,
                'is_frozen'           => (bool) $savings->is_frozen,
                'transaksi_hari_ini'  => (float) $transaksi_hari_ini,
                'saldo_dapat_dipakai' => $savings->is_frozen
                    ? 0
                    : max(0, (float) $savings->limit_harian - (float) $transaksi_hari_ini),
                'last_transaction_at' => $savings->last_transaction_at,
            ],
        ]);
    }

    /**
     * POST /api/v1/wali/santri/{studentId}/tabungan/limit
     * Set limit jajan harian.
     */
    public function setLimitTabungan(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $request->validate([
            'limit_harian' => 'required|numeric|min:0|max:500000',
        ]);

        DB::table('student_savings')->updateOrInsert(
            ['student_id' => $studentId],
            ['limit_harian' => $request->limit_harian, 'updated_at' => now()]
        );

        return response()->json([
            'ok'      => true,
            'message' => 'Limit jajan berhasil diperbarui.',
            'data'    => ['limit_harian' => (float) $request->limit_harian],
        ]);
    }

    /**
     * POST /api/v1/wali/santri/{studentId}/tabungan/freeze
     * Bekukan atau aktifkan tabungan.
     */
    public function freezeTabungan(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $request->validate([
            'freeze' => 'required|boolean',
        ]);

        DB::table('student_savings')->updateOrInsert(
            ['student_id' => $studentId],
            ['is_frozen' => $request->freeze, 'updated_at' => now()]
        );

        $status = $request->freeze ? 'dibekukan' : 'diaktifkan kembali';

        return response()->json([
            'ok'      => true,
            'message' => "Tabungan berhasil {$status}.",
            'data'    => ['is_frozen' => (bool) $request->freeze],
        ]);
    }

    /**
     * POST /api/v1/wali/santri/{studentId}/tabungan/topup
     * Setor saldo ke tabungan santri (upload bukti transfer / QRIS).
     * Saldo baru masuk setelah admin memverifikasi bukti.
     */
    public function topupTabungan(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $validated = $request->validate([
            'foto'             => 'required|image|max:5120',
            'nominal_transfer' => 'required|numeric|min:10000',
            'tanggal_transfer' => 'required|date',
            'bank_pengirim'    => 'nullable|string|max:50',
            'nama_pengirim'    => 'nullable|string|max:100',
            'catatan'          => 'nullable|string|max:500',
        ]);

        $path = $request->file('foto')->store('payment-proofs', 'public');

        $proof = PaymentProof::create([
            'type'             => 'topup',
            'invoice_id'       => null,
            'student_id'       => $studentId,
            'erp_account_id'   => $request->user()->id,
            'file_path'        => $path,
            'nominal_transfer' => $validated['nominal_transfer'],
            'tanggal_transfer' => $validated['tanggal_transfer'],
            'bank_pengirim'    => $request->bank_pengirim,
            'nama_pengirim'    => $request->nama_pengirim,
            'notes'            => $request->catatan,
            'status'           => 'pending',
        ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Bukti setoran berhasil dikirim. Saldo akan ditambahkan setelah dikonfirmasi admin.',
            'data'    => ['id' => $proof->id, 'status' => $proof->status],
        ], 201);
    }

    /**
     * GET /api/v1/wali/santri/{studentId}/transaksi
     * Riwayat transaksi tabungan.
     */
    public function transaksi(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $perPage = min((int) $request->get('per_page', 20), 50);

        $transaksi = DB::table('savings_transactions')
            ->where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'ok'   => true,
            'data' => collect($transaksi->items())->map(fn($t) => [
                'id'           => $t->id,
                'tanggal'      => date('Y-m-d', strtotime($t->created_at)),
                'waktu'        => date('H:i', strtotime($t->created_at)),
                'jenis'        => $t->jenis,
                'kategori'     => $t->kategori,
                'nominal'      => (float) $t->nominal,
                'saldo_sesudah'=> (float) $t->saldo_sesudah,
                'keterangan'   => $t->keterangan,
            ]),
            'meta' => [
                'current_page' => $transaksi->currentPage(),
                'last_page'    => $transaksi->lastPage(),
                'per_page'     => $transaksi->perPage(),
                'total'        => $transaksi->total(),
            ],
        ]);
    }

    // ─── Akademik ─────────────────────────────────────────────

    /**
     * GET /api/v1/wali/santri/{studentId}/jadwal?hari={0-6}
     * Jadwal pelajaran. Tanpa param hari = semua hari.
     */
    public function jadwal(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $query = DB::table('class_schedules')
            ->where('student_id', $studentId)
            ->where('is_active', true)
            ->orderBy('hari')
            ->orderBy('jam_mulai');

        if ($request->has('hari')) {
            $query->where('hari', (int) $request->hari);
        }

        $hariLabel = ['Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $jadwal = $query->get()->map(fn($j) => [
            'id'            => $j->id,
            'hari'          => $j->hari,
            'hari_label'    => $hariLabel[$j->hari] ?? '-',
            'jam_mulai'     => $j->jam_mulai,
            'jam_selesai'   => $j->jam_selesai,
            'mata_pelajaran'=> $j->mata_pelajaran,
            'guru'          => $j->guru,
            'ruang'         => $j->ruang,
            'keterangan'    => $j->keterangan,
        ]);

        return response()->json(['ok' => true, 'data' => $jadwal]);
    }

    /**
     * GET /api/v1/wali/santri/{studentId}/absensi?bulan=5&tahun=2026
     * Data absensi bulanan.
     */
    public function absensi(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $records = StudentAttendance::where('student_id', $studentId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->get();

        $rekap = $records->groupBy('status_kehadiran')
            ->map(fn($group) => $group->count());

        return response()->json([
            'ok'     => true,
            'bulan'  => $bulan,
            'tahun'  => $tahun,
            'rekap'  => [
                'hadir' => $rekap['hadir'] ?? 0,
                'sakit' => $rekap['sakit'] ?? 0,
                'izin'  => $rekap['izin']  ?? 0,
                'alfa'  => $rekap['alfa']  ?? 0,
            ],
            'detail' => $records->map(fn($a) => $this->formatAbsensi($a)),
        ]);
    }

    /**
     * GET /api/v1/wali/santri/{studentId}/akademik?semester=1&tahun=2026
     * Nilai akademik per semester.
     */
    public function akademik(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $semester  = (int) $request->get('semester', 1);
        $tahunAjaran = $request->get('tahun', now()->year) . '/' . ($request->get('tahun', now()->year) + 1);

        $period = DB::table('academic_periods')
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->first();

        if (!$period) {
            return response()->json(['ok' => true, 'data' => [], 'rata_rata' => null, 'ranking' => null]);
        }

        $nilai = DB::table('student_grades')
            ->where('student_id', $studentId)
            ->where('period_id', $period->id)
            ->orderBy('mata_pelajaran')
            ->get()
            ->map(fn($n) => [
                'mata_pelajaran' => $n->mata_pelajaran,
                'kkm'            => $n->kkm,
                'nilai_harian'   => $n->nilai_harian !== null ? (float) $n->nilai_harian : null,
                'nilai_uts'      => $n->nilai_uts !== null ? (float) $n->nilai_uts : null,
                'nilai_uas'      => $n->nilai_uas !== null ? (float) $n->nilai_uas : null,
                'nilai_akhir'    => $n->nilai_akhir !== null ? (float) $n->nilai_akhir : null,
                'predikat'       => $n->predikat,
                'lulus'          => $n->nilai_akhir !== null && $n->nilai_akhir >= $n->kkm,
            ]);

        $firstRow = DB::table('student_grades')
            ->where('student_id', $studentId)
            ->where('period_id', $period->id)
            ->whereNotNull('ranking_kelas')
            ->first();

        $rata = $nilai->whereNotNull('nilai_akhir')->avg('nilai_akhir');

        return response()->json([
            'ok'           => true,
            'periode'      => $period->label ?? "{$period->tahun_ajaran} Semester {$period->semester}",
            'rata_rata'    => $rata ? round($rata, 2) : null,
            'ranking'      => $firstRow?->ranking_kelas,
            'total_siswa'  => $firstRow?->total_siswa_kelas,
            'data'         => $nilai,
        ]);
    }

    /**
     * GET /api/v1/wali/santri/{studentId}/prestasi
     * Daftar prestasi & penghargaan.
     */
    public function prestasi(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $prestasi = DB::table('student_achievements')
            ->where('student_id', $studentId)
            ->where('is_published', true)
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'judul'        => $p->judul,
                'kategori'     => $p->kategori,
                'tingkat'      => $p->tingkat,
                'peringkat'    => $p->peringkat,
                'penyelenggara'=> $p->penyelenggara,
                'tanggal'      => $p->tanggal,
                'keterangan'   => $p->keterangan,
                'foto'         => $p->foto_path ? Storage::url($p->foto_path) : null,
            ]);

        return response()->json(['ok' => true, 'data' => $prestasi]);
    }

    // ─── Kegiatan ─────────────────────────────────────────────

    /**
     * GET /api/v1/wali/santri/{studentId}/kesehatan
     * Riwayat kunjungan UKS.
     */
    public function kesehatan(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $records = DB::table('health_records')
            ->leftJoin('erp_accounts', 'health_records.petugas_id', '=', 'erp_accounts.id')
            ->where('health_records.student_id', $studentId)
            ->orderByDesc('health_records.tanggal')
            ->select('health_records.*', 'erp_accounts.full_name as petugas_nama')
            ->limit(30)
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'tanggal'      => $r->tanggal,
                'keluhan'      => $r->keluhan,
                'diagnosa'     => $r->diagnosa,
                'penanganan'   => $r->penanganan,
                'obat'         => $r->obat,
                'tekanan_darah'=> $r->tekanan_darah,
                'suhu_tubuh'   => $r->suhu_tubuh ? (float) $r->suhu_tubuh : null,
                'berat_badan'  => $r->berat_badan ? (float) $r->berat_badan : null,
                'tinggi_badan' => $r->tinggi_badan ? (float) $r->tinggi_badan : null,
                'dirujuk_ke'   => $r->dirujuk_ke,
                'petugas'      => $r->petugas_nama,
            ]);

        return response()->json(['ok' => true, 'data' => $records]);
    }

    /**
     * GET /api/v1/wali/santri/{studentId}/kunjungan
     * Riwayat & jadwal kunjungan wali.
     */
    public function daftarKunjungan(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $kunjungan = DB::table('parent_visits')
            ->where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($k) => [
                'id'               => $k->id,
                'tanggal_rencana'  => $k->tanggal_rencana,
                'jam_rencana'      => $k->jam_rencana,
                'tanggal_aktual'   => $k->tanggal_aktual,
                'jam_datang'       => $k->jam_datang,
                'jam_pulang'       => $k->jam_pulang,
                'jumlah_pengunjung'=> $k->jumlah_pengunjung,
                'status'           => $k->status,
                'status_label'     => $this->kunjunganStatusLabel($k->status),
                'keterangan'       => $k->keterangan,
                'catatan_staff'    => $k->catatan_staff,
            ]);

        return response()->json(['ok' => true, 'data' => $kunjungan]);
    }

    /**
     * POST /api/v1/wali/santri/{studentId}/kunjungan
     * Ajukan jadwal kunjungan.
     */
    public function ajukanKunjungan(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $request->validate([
            'tanggal_rencana'  => 'required|date|after_or_equal:today',
            'jam_rencana'      => 'nullable|date_format:H:i',
            'jumlah_pengunjung'=> 'nullable|integer|min:1|max:10',
            'keterangan'       => 'nullable|string|max:500',
        ]);

        $id = DB::table('parent_visits')->insertGetId([
            'student_id'       => $studentId,
            'wali_account_id'  => $request->user()->id,
            'tanggal_rencana'  => $request->tanggal_rencana,
            'jam_rencana'      => $request->jam_rencana,
            'jumlah_pengunjung'=> $request->get('jumlah_pengunjung', 1),
            'status'           => 'menunggu',
            'keterangan'       => $request->keterangan,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Permohonan kunjungan berhasil dikirim. Menunggu konfirmasi.',
            'data'    => ['id' => $id, 'status' => 'menunggu'],
        ], 201);
    }

    /**
     * GET /api/v1/wali/santri/{studentId}/konseling
     * Riwayat sesi konseling.
     */
    public function daftarKonseling(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $konseling = DB::table('counseling_sessions')
            ->leftJoin('erp_accounts', 'counseling_sessions.konselor_id', '=', 'erp_accounts.id')
            ->where('counseling_sessions.student_id', $studentId)
            ->orderByDesc('counseling_sessions.created_at')
            ->select('counseling_sessions.*', 'erp_accounts.full_name as konselor_nama')
            ->limit(20)
            ->get()
            ->map(fn($k) => [
                'id'                 => $k->id,
                'topik'              => $k->topik,
                'tanggal_preferensi' => $k->tanggal_preferensi,
                'tanggal_aktual'     => $k->tanggal_aktual,
                'jam_aktual'         => $k->jam_aktual,
                'konselor'           => $k->konselor_nama,
                'status'             => $k->status,
                'status_label'       => $this->konselingStatusLabel($k->status),
                'catatan_konselor'   => $k->catatan_konselor,
                'tindak_lanjut'      => $k->tindak_lanjut,
                'dibuat'             => date('Y-m-d H:i', strtotime($k->created_at)),
            ]);

        return response()->json(['ok' => true, 'data' => $konseling]);
    }

    /**
     * POST /api/v1/wali/santri/{studentId}/konseling
     * Ajukan permintaan sesi konseling.
     */
    public function ajukanKonseling(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $request->validate([
            'topik'              => 'required|string|min:5|max:200',
            'pesan'              => 'nullable|string|max:1000',
            'tanggal_preferensi' => 'nullable|date|after_or_equal:today',
        ]);

        $id = DB::table('counseling_sessions')->insertGetId([
            'student_id'         => $studentId,
            'wali_account_id'    => $request->user()->id,
            'topik'              => $request->topik,
            'pesan'              => $request->pesan,
            'tanggal_preferensi' => $request->tanggal_preferensi,
            'status'             => 'menunggu',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Permintaan konseling berhasil dikirim.',
            'data'    => ['id' => $id, 'status' => 'menunggu'],
        ], 201);
    }

    /**
     * GET /api/v1/wali/santri/{studentId}/presensi?bulan=5&tahun=2026
     * Presensi kegiatan pondok.
     */
    public function presensi(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $records = DB::table('activity_attendances')
            ->leftJoin('activities', 'activity_attendances.activity_id', '=', 'activities.id')
            ->where('activity_attendances.student_id', $studentId)
            ->whereMonth('activity_attendances.tanggal', $bulan)
            ->whereYear('activity_attendances.tanggal', $tahun)
            ->orderByDesc('activity_attendances.tanggal')
            ->select(
                'activity_attendances.*',
                DB::raw('COALESCE(activity_attendances.nama_kegiatan, activities.nama) as kegiatan'),
                'activities.kategori as kategori_kegiatan'
            )
            ->get()
            ->map(fn($r) => [
                'id'       => $r->id,
                'tanggal'  => $r->tanggal,
                'kegiatan' => $r->kegiatan,
                'kategori' => $r->kategori_kegiatan,
                'sesi'     => $r->sesi,
                'status'   => $r->status,
                'keterangan'=> $r->keterangan,
            ]);

        return response()->json([
            'ok'    => true,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'data'  => $records,
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────

    private function authorizeStudent(Request $request, int $studentId): void
    {
        $wali = $request->user();
        $student = Student::where('id', $studentId)
            ->where('wali_account_id', $wali->id)
            ->firstOrFail();
    }

    private function formatAbsensi(StudentAttendance $a): array
    {
        return [
            'id'               => $a->id,
            'tanggal'          => $a->tanggal->format('Y-m-d'),
            'sesi'             => $a->sesi,
            'status_kehadiran' => $a->status_kehadiran,
            'status_label'     => $a->status_label,
            'status_kesehatan' => $a->status_kesehatan,
            'kesehatan_label'  => $a->kesehatan_label,
            'keterangan'       => $a->keterangan,
        ];
    }

    private function formatIzin(StudentPermission $i): array
    {
        return [
            'id'             => $i->id,
            'jenis'          => $i->jenis_izin,
            'jenis_label'    => $i->jenis_label,
            'tanggal_mulai'  => $i->tanggal_mulai->format('Y-m-d'),
            'tanggal_selesai'=> $i->tanggal_selesai?->format('Y-m-d'),
            'jam_keluar'     => $i->jam_keluar,
            'jam_kembali'    => $i->jam_kembali,
            'alasan'         => $i->alasan,
            'tujuan'         => $i->tujuan,
            'status'         => $i->status,
            'status_label'   => $i->status_label,
            'catatan_staff'  => $i->catatan_staff,
            'dibuat'         => $i->created_at->format('Y-m-d H:i'),
        ];
    }

    private function kunjunganStatusLabel(string $status): string
    {
        return match($status) {
            'menunggu'   => 'Menunggu Konfirmasi',
            'disetujui'  => 'Disetujui',
            'selesai'    => 'Selesai',
            'batal'      => 'Dibatalkan',
            default      => $status,
        };
    }

    private function konselingStatusLabel(string $status): string
    {
        return match($status) {
            'menunggu'     => 'Menunggu',
            'dijadwalkan'  => 'Dijadwalkan',
            'selesai'      => 'Selesai',
            'batal'        => 'Dibatalkan',
            default        => $status,
        };
    }
}
