<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            // === Data Pribadi & Identitas ===
            $table->string('nisn', 20)->nullable()->after('nik');
            $table->string('kebangsaan', 50)->nullable()->default('Indonesia')->after('gender');
            $table->string('golongan_darah', 5)->nullable()->after('kebangsaan');
            $table->string('hobi')->nullable()->after('golongan_darah');
            $table->string('cita_cita')->nullable()->after('hobi');
            $table->string('pendidikan_terakhir')->nullable()->after('cita_cita');
            $table->string('yang_membiayai')->nullable()->after('pendidikan_terakhir');
            $table->string('kebutuhan_khusus')->nullable()->after('yang_membiayai');
            $table->string('kebutuhan_disabilitas')->nullable()->after('kebutuhan_khusus');
            $table->string('foto_url')->nullable()->after('kebutuhan_disabilitas');

            // === Data Keluarga & Domisili ===
            $table->string('no_kk', 20)->nullable()->after('foto_url');
            $table->string('nama_kepala_keluarga')->nullable()->after('no_kk');
            $table->integer('anak_ke')->nullable()->after('nama_kepala_keluarga');
            $table->integer('jumlah_saudara')->nullable()->after('anak_ke');
            $table->string('status_rumah')->nullable()->after('jumlah_saudara');
            $table->string('alamat_jalan')->nullable()->after('address');
            $table->string('rt', 5)->nullable()->after('alamat_jalan');
            $table->string('rw', 5)->nullable()->after('rt');
            $table->string('desa_kelurahan')->nullable()->after('rw');
            $table->string('kecamatan')->nullable()->after('desa_kelurahan');
            $table->string('kab_kota')->nullable()->after('kecamatan');
            $table->string('provinsi')->nullable()->after('kab_kota');
            $table->string('kode_pos', 10)->nullable()->after('provinsi');

            // === Data Ayah ===
            $table->string('ayah_nama')->nullable()->after('kode_pos');
            $table->string('ayah_status', 20)->nullable()->after('ayah_nama'); // hidup/meninggal
            $table->string('ayah_nik', 20)->nullable()->after('ayah_status');
            $table->string('ayah_tempat_lahir')->nullable()->after('ayah_nik');
            $table->date('ayah_tanggal_lahir')->nullable()->after('ayah_tempat_lahir');
            $table->string('ayah_pekerjaan')->nullable()->after('ayah_tanggal_lahir');
            $table->string('ayah_pendidikan')->nullable()->after('ayah_pekerjaan');
            $table->string('ayah_penghasilan')->nullable()->after('ayah_pendidikan');
            $table->string('ayah_no_telepon', 20)->nullable()->after('ayah_penghasilan');
            $table->text('ayah_alamat')->nullable()->after('ayah_no_telepon');

            // === Data Ibu ===
            $table->string('ibu_nama')->nullable()->after('ayah_alamat');
            $table->string('ibu_status', 20)->nullable()->after('ibu_nama');
            $table->string('ibu_nik', 20)->nullable()->after('ibu_status');
            $table->string('ibu_tempat_lahir')->nullable()->after('ibu_nik');
            $table->date('ibu_tanggal_lahir')->nullable()->after('ibu_tempat_lahir');
            $table->string('ibu_pekerjaan')->nullable()->after('ibu_tanggal_lahir');
            $table->string('ibu_pendidikan')->nullable()->after('ibu_pekerjaan');
            $table->string('ibu_penghasilan')->nullable()->after('ibu_pendidikan');
            $table->string('ibu_no_telepon', 20)->nullable()->after('ibu_penghasilan');
            $table->text('ibu_alamat')->nullable()->after('ibu_no_telepon');

            // === Data Wali ===
            $table->string('wali_nama')->nullable()->after('ibu_alamat');
            $table->string('wali_hubungan')->nullable()->after('wali_nama');
            $table->string('wali_status', 20)->nullable()->after('wali_hubungan');
            $table->string('wali_nik', 20)->nullable()->after('wali_status');
            $table->string('wali_tempat_lahir')->nullable()->after('wali_nik');
            $table->date('wali_tanggal_lahir')->nullable()->after('wali_tempat_lahir');
            $table->string('wali_pekerjaan')->nullable()->after('wali_tanggal_lahir');
            $table->string('wali_pendidikan')->nullable()->after('wali_pekerjaan');
            $table->string('wali_penghasilan')->nullable()->after('wali_pendidikan');
            $table->string('wali_no_telepon', 20)->nullable()->after('wali_penghasilan');
            $table->text('wali_alamat')->nullable()->after('wali_no_telepon');
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'nisn', 'kebangsaan', 'golongan_darah', 'hobi', 'cita_cita',
                'pendidikan_terakhir', 'yang_membiayai', 'kebutuhan_khusus', 'kebutuhan_disabilitas', 'foto_url',
                'no_kk', 'nama_kepala_keluarga', 'anak_ke', 'jumlah_saudara', 'status_rumah',
                'alamat_jalan', 'rt', 'rw', 'desa_kelurahan', 'kecamatan', 'kab_kota', 'provinsi', 'kode_pos',
                'ayah_nama', 'ayah_status', 'ayah_nik', 'ayah_tempat_lahir', 'ayah_tanggal_lahir',
                'ayah_pekerjaan', 'ayah_pendidikan', 'ayah_penghasilan', 'ayah_no_telepon', 'ayah_alamat',
                'ibu_nama', 'ibu_status', 'ibu_nik', 'ibu_tempat_lahir', 'ibu_tanggal_lahir',
                'ibu_pekerjaan', 'ibu_pendidikan', 'ibu_penghasilan', 'ibu_no_telepon', 'ibu_alamat',
                'wali_nama', 'wali_hubungan', 'wali_status', 'wali_nik', 'wali_tempat_lahir', 'wali_tanggal_lahir',
                'wali_pekerjaan', 'wali_pendidikan', 'wali_penghasilan', 'wali_no_telepon', 'wali_alamat',
            ]);
        });
    }
};
