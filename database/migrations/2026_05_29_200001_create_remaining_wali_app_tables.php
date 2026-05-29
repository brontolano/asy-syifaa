<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel-tabel yang diperlukan untuk melengkapi 21 endpoint PWA Wali Santri.
 * Sudah ada: student_attendances, tahfidz_*, student_permissions, invoices, payment_proofs
 * Belum ada: student_savings, class_schedules, academic_periods, student_grades,
 *             student_achievements, health_records, parent_visits,
 *             counseling_sessions, activities, activity_attendances
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabungan Santri
        Schema::create('student_savings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete()->unique();
            $table->decimal('saldo', 14, 2)->default(0);
            $table->decimal('limit_harian', 14, 2)->default(30000)->comment('Limit jajan per hari');
            $table->boolean('is_frozen')->default(false)->comment('Jika true, tabungan tidak bisa dipakai');
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();
        });

        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('jenis', ['kredit', 'debit'])->comment('kredit=masuk, debit=keluar');
            $table->string('kategori', 30)->nullable()->comment('setor_wali, jajan, pembelian, koreksi, dll');
            $table->decimal('nominal', 14, 2)->unsigned();
            $table->decimal('saldo_sesudah', 14, 2);
            $table->string('keterangan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
        });

        // 2. Jadwal Pelajaran
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedTinyInteger('hari')->comment('0=Ahad, 1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu');
            $table->string('jam_mulai', 6)->comment('Format: 07.00');
            $table->string('jam_selesai', 6)->comment('Format: 08.30');
            $table->string('mata_pelajaran', 100);
            $table->string('guru', 100)->nullable();
            $table->string('ruang', 50)->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['student_id', 'hari']);
        });

        // 3. Periode Akademik
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_ajaran', 9)->comment('Contoh: 2025/2026');
            $table->unsignedTinyInteger('semester')->comment('1 atau 2');
            $table->string('label', 50)->nullable()->comment('Contoh: Semester 1 TA 2025/2026');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tahun_ajaran', 'semester']);
        });

        // 4. Nilai Akademik Santri
        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('academic_periods')->cascadeOnDelete();
            $table->string('mata_pelajaran', 100);
            $table->unsignedSmallInteger('kkm')->default(75);
            $table->decimal('nilai_harian', 5, 2)->nullable();
            $table->decimal('nilai_uts', 5, 2)->nullable();
            $table->decimal('nilai_uas', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('predikat', 2)->nullable()->comment('A/B/C/D');
            $table->unsignedSmallInteger('ranking_kelas')->nullable();
            $table->unsignedSmallInteger('total_siswa_kelas')->nullable();
            $table->foreignId('input_oleh')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'period_id', 'mata_pelajaran']);
            $table->index(['student_id', 'period_id']);
        });

        // 5. Prestasi Santri
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('judul', 200);
            $table->string('kategori', 50)->nullable()
                ->comment('akademik, hafalan, olahraga, seni, lainnya');
            $table->string('tingkat', 30)->nullable()
                ->comment('internal, kecamatan, kabupaten, provinsi, nasional, internasional');
            $table->string('peringkat', 30)->nullable()
                ->comment('Juara 1, Juara 2, Juara 3, Finalis, Peserta, dll');
            $table->string('penyelenggara', 100)->nullable();
            $table->date('tanggal')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('foto_path', 255)->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index('student_id');
        });

        // 6. Rekam Medis / Kesehatan UKS
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('keluhan')->nullable();
            $table->string('diagnosa', 200)->nullable();
            $table->text('penanganan')->nullable();
            $table->string('obat', 500)->nullable();
            $table->string('tekanan_darah', 20)->nullable()->comment('Contoh: 120/80');
            $table->decimal('suhu_tubuh', 4, 1)->nullable()->comment('Derajat Celsius');
            $table->decimal('berat_badan', 5, 2)->nullable()->comment('Kilogram');
            $table->decimal('tinggi_badan', 5, 2)->nullable()->comment('Sentimeter');
            $table->string('dirujuk_ke', 100)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('petugas_id')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'tanggal']);
        });

        // 7. Kunjungan Wali ke Pesantren
        Schema::create('parent_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('wali_account_id')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->date('tanggal_rencana')->nullable();
            $table->time('jam_rencana')->nullable();
            $table->date('tanggal_aktual')->nullable();
            $table->time('jam_datang')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->unsignedSmallInteger('jumlah_pengunjung')->default(1);
            $table->enum('status', ['menunggu', 'disetujui', 'selesai', 'batal'])->default('menunggu');
            $table->text('keterangan')->nullable();
            $table->text('catatan_staff')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });

        // 8. Sesi Konseling
        Schema::create('counseling_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('wali_account_id')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->string('topik', 200);
            $table->text('pesan')->nullable()->comment('Pesan dari wali saat mengajukan');
            $table->date('tanggal_preferensi')->nullable();
            $table->date('tanggal_aktual')->nullable();
            $table->time('jam_aktual')->nullable();
            $table->foreignId('konselor_id')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->enum('status', ['menunggu', 'dijadwalkan', 'selesai', 'batal'])->default('menunggu');
            $table->text('catatan_konselor')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });

        // 9. Katalog Kegiatan Pondok
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kategori', 50)->nullable()
                ->comment('sholat, belajar, ekskul, acara, muhadhoroh, olahraga, lainnya');
            $table->boolean('is_recurring')->default(true)->comment('Kegiatan rutin harian/mingguan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 10. Presensi Kegiatan Santri
        Schema::create('activity_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->string('nama_kegiatan', 100)->nullable()->comment('Fallback jika activity_id null');
            $table->date('tanggal');
            $table->string('sesi', 30)->nullable()->comment('pagi, siang, sore, malam, dll');
            $table->enum('status', ['hadir', 'tidak_hadir', 'izin', 'sakit'])->default('hadir');
            $table->string('keterangan', 255)->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_attendances');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('counseling_sessions');
        Schema::dropIfExists('parent_visits');
        Schema::dropIfExists('health_records');
        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('student_grades');
        Schema::dropIfExists('academic_periods');
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('savings_transactions');
        Schema::dropIfExists('student_savings');
    }
};
