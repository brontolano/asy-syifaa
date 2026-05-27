<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create students table — separate from ppdb_registrations
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 20)->unique()->comment('Nomer Induk Siswa');
            $table->string('nisn', 20)->nullable()->comment('Nomer Induk Siswa Nasional');
            $table->string('nik', 20)->nullable();
            $table->string('full_name');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['L', 'P']);
            $table->string('kelas', 20)->nullable();
            $table->string('kelas_detail', 20)->nullable();
            $table->string('rombel', 50)->nullable();
            $table->string('jenjang', 20)->nullable()->comment('Wustha/Ulya/Tamhidi');
            $table->string('tahun_masuk', 4)->nullable();
            $table->string('tahun_keluar', 4)->nullable();
            $table->enum('status', ['aktif', 'waqof', 'alumni', 'pengabdian', 'tendik', 'mutasi', 'dikeluarkan'])->default('aktif');
            $table->string('jalur_masuk', 30)->default('reguler')->comment('reguler/yatim/beasiswa/tahfidz');

            // Personal
            $table->string('kebangsaan', 10)->default('WNI');
            $table->string('golongan_darah', 5)->nullable();
            $table->string('hobi', 100)->nullable();
            $table->string('cita_cita', 100)->nullable();
            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->string('yang_membiayai', 50)->nullable();
            $table->string('kebutuhan_khusus', 50)->nullable();
            $table->string('kebutuhan_disabilitas', 50)->nullable();
            $table->integer('anak_ke')->nullable();
            $table->integer('jumlah_saudara')->nullable();

            // Family - KK
            $table->string('no_kk', 20)->nullable();
            $table->string('nama_kepala_keluarga')->nullable();

            // Ayah
            $table->string('ayah_nama')->nullable();
            $table->string('ayah_status', 30)->nullable();
            $table->string('ayah_nik', 20)->nullable();
            $table->string('ayah_tempat_lahir')->nullable();
            $table->date('ayah_tanggal_lahir')->nullable();
            $table->string('ayah_pekerjaan', 100)->nullable();
            $table->string('ayah_pendidikan', 50)->nullable();
            $table->string('ayah_no_telepon', 20)->nullable();
            $table->string('ayah_penghasilan', 50)->nullable();
            $table->text('ayah_alamat')->nullable();

            // Ibu
            $table->string('ibu_nama')->nullable();
            $table->string('ibu_status', 30)->nullable();
            $table->string('ibu_nik', 20)->nullable();
            $table->string('ibu_tempat_lahir')->nullable();
            $table->date('ibu_tanggal_lahir')->nullable();
            $table->string('ibu_pekerjaan', 100)->nullable();
            $table->string('ibu_pendidikan', 50)->nullable();
            $table->string('ibu_no_telepon', 20)->nullable();
            $table->string('ibu_penghasilan', 50)->nullable();
            $table->text('ibu_alamat')->nullable();

            // Wali
            $table->string('wali_status', 30)->nullable()->comment('Hubungan wali: Ayah Kandung, dll');
            $table->string('wali_nama')->nullable();
            $table->string('wali_nik', 20)->nullable();
            $table->string('wali_tempat_lahir')->nullable();
            $table->date('wali_tanggal_lahir')->nullable();
            $table->string('wali_pekerjaan', 100)->nullable();
            $table->string('wali_pendidikan', 50)->nullable();
            $table->string('wali_no_telepon', 20)->nullable();
            $table->string('wali_penghasilan', 50)->nullable();
            $table->text('wali_alamat')->nullable();

            // Alamat Santri
            $table->string('status_rumah', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->string('rt_rw', 10)->nullable();
            $table->string('desa_kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kab_kota', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kode_pos', 10)->nullable();

            // Links
            $table->foreignId('erp_account_id')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->foreignId('ppdb_registration_id')->nullable()->constrained('ppdb_registrations')->nullOnDelete();

            // SPP
            $table->decimal('spp_amount', 12, 2)->default(750000)->comment('Custom SPP per student');
            $table->integer('tunggakan_bulan')->default(0)->comment('Jumlah bulan tunggakan');
            $table->date('waqof_at')->nullable();
            $table->text('waqof_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Hijri billing periods
        Schema::create('hijri_billing_periods', function (Blueprint $table) {
            $table->id();
            $table->string('hijri_month', 2)->comment('1-12');
            $table->string('hijri_year', 4);
            $table->string('hijri_month_name', 30)->comment('Muharram, Safar, etc');
            $table->string('label')->comment('Muharram 1447 H');
            $table->date('gregorian_start')->comment('Perkiraan awal bulan Hijriah');
            $table->date('gregorian_end')->comment('Perkiraan akhir bulan Hijriah');
            $table->date('due_date')->comment('Jatuh tempo pembayaran');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['hijri_month', 'hijri_year']);
        });

        // 3. Enhance invoices — add student_id FK and hijri_period
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('student_id_fk')->nullable()->after('student_id')
                ->constrained('students')->nullOnDelete();
            $table->foreignId('hijri_billing_period_id')->nullable()->after('billing_period_id')
                ->constrained('hijri_billing_periods')->nullOnDelete();
            $table->string('invoice_type', 30)->default('spp')->after('invoice_number')
                ->comment('spp, ujian, muadalah, daftar_ulang, additional');
            $table->string('hijri_label')->nullable()->after('invoice_type');
        });

        // 4. Enhance payments — add payment channel info
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_channel', 30)->nullable()->after('payment_method')
                ->comment('cash, transfer_bsi, transfer_bca, etc');
            $table->foreignId('student_id')->nullable()->after('invoice_id')
                ->constrained('students')->nullOnDelete();
        });

        // 5. Waqof log
        Schema::create('waqof_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('action', ['waqof', 'reactivate']);
            $table->text('reason')->nullable();
            $table->integer('tunggakan_bulan')->default(0);
            $table->decimal('tunggakan_amount', 14, 2)->default(0);
            $table->foreignId('actioned_by')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->timestamps();
        });

        // 6. Payment method config
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn(['payment_channel', 'student_id']);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['student_id_fk']);
            $table->dropForeign(['hijri_billing_period_id']);
            $table->dropColumn(['student_id_fk', 'hijri_billing_period_id', 'invoice_type', 'hijri_label']);
        });
        Schema::dropIfExists('waqof_logs');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('hijri_billing_periods');
        Schema::dropIfExists('students');
    }
};
