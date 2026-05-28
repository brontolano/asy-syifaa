<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pendukung Asy-Syifaa App (PWA Wali Santri)
 * Data input oleh staff ERP, dikonsumsi via API oleh app wali.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Absensi & Status Harian Santri
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('status_kehadiran', ['hadir', 'sakit', 'izin', 'alfa'])->default('hadir');
            $table->enum('status_kesehatan', ['sehat', 'sakit_ringan', 'sakit_berat', 'rujukan'])->default('sehat');
            $table->string('keterangan')->nullable();
            $table->string('sesi', 20)->default('pagi')->comment('pagi, siang, malam');
            $table->foreignId('dicatat_oleh')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'tanggal', 'sesi']);
            $table->index(['student_id', 'tanggal']);
        });

        // 2. Rekap Hafalan / Tahfidz Santri
        Schema::create('tahfidz_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('tanggal_setor');
            $table->enum('kategori', ['quran', 'hadist', 'mufrodat', 'materi'])->default('quran');
            $table->string('pencapaian')->comment('Contoh: Juz 1, Al-Baqarah 1-10, Hadist ke-5');
            $table->string('halaman_dari')->nullable();
            $table->string('halaman_sampai')->nullable();
            $table->integer('jumlah_ayat')->nullable();
            $table->enum('jenis_setor', ['ziyadah', 'murajaah', 'tasmi'])->default('ziyadah');
            $table->enum('nilai', ['A', 'B', 'C', 'D'])->nullable()->comment('A=Mumtaz, B=Jayyid, C=Maqbul, D=Rajih');
            $table->text('catatan_ustadz')->nullable();
            $table->foreignId('ustadz_id')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'tanggal_setor']);
        });

        // 3. Progress Total Hafalan per Santri (summary)
        Schema::create('tahfidz_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete()->unique();
            $table->integer('total_juz_quran')->default(0)->comment('Total juz yang sudah dihafal');
            $table->integer('target_juz_quran')->default(30);
            $table->integer('total_hadist')->default(0);
            $table->integer('target_hadist')->default(0);
            $table->date('update_terakhir')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 4. Perizinan Santri (Izin Keluar / Pulang)
        Schema::create('student_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('pengaju_wali_id')->nullable()->constrained('erp_accounts')->nullOnDelete()
                ->comment('Akun wali yang mengajukan izin');
            $table->enum('jenis_izin', ['pulang', 'keluar_area', 'kegiatan_luar', 'sakit', 'lainnya'])->default('pulang');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->time('jam_kembali')->nullable();
            $table->text('alasan');
            $table->string('tujuan')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'selesai'])->default('menunggu');
            $table->text('catatan_staff')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->timestamp('diproses_at')->nullable();
            $table->timestamp('santri_keluar_at')->nullable();
            $table->timestamp('santri_kembali_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['status', 'tanggal_mulai']);
        });

        // 5. Tambah kolom wali-app ke payment_proofs yang sudah ada
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('invoice_id')
                ->constrained('students')->nullOnDelete();
            $table->decimal('nominal_transfer', 12, 2)->nullable()->after('file_path');
            $table->date('tanggal_transfer')->nullable()->after('nominal_transfer');
            $table->string('bank_pengirim', 50)->nullable()->after('tanggal_transfer');
            $table->string('nama_pengirim')->nullable()->after('bank_pengirim');
        });
    }

    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn(['student_id', 'nominal_transfer', 'tanggal_transfer', 'bank_pengirim', 'nama_pengirim']);
        });
        Schema::dropIfExists('student_permissions');
        Schema::dropIfExists('tahfidz_progress');
        Schema::dropIfExists('tahfidz_records');
        Schema::dropIfExists('student_attendances');
    }
};
