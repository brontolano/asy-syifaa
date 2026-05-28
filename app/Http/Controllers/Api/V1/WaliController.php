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

        $request->validate([
            'foto'            => 'required|image|max:5120',
            'nominal'         => 'required|numeric|min:1000',
            'tanggal_transfer'=> 'required|date',
            'bank_pengirim'   => 'nullable|string|max:50',
            'nama_pengirim'   => 'nullable|string|max:100',
            'catatan'         => 'nullable|string|max:500',
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
            'nominal_transfer' => $request->nominal,
            'tanggal_transfer' => $request->tanggal_transfer,
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
}
