<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Lengkapi kolom payment_proofs agar sesuai data dari PWA Wali
        Schema::table('payment_proofs', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_proofs', 'student_id')) {
                $table->unsignedBigInteger('student_id')->nullable()->after('invoice_id');
            }
            if (!Schema::hasColumn('payment_proofs', 'nominal_transfer')) {
                $table->decimal('nominal_transfer', 15, 2)->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('payment_proofs', 'tanggal_transfer')) {
                $table->date('tanggal_transfer')->nullable()->after('nominal_transfer');
            }
            if (!Schema::hasColumn('payment_proofs', 'bank_pengirim')) {
                $table->string('bank_pengirim', 50)->nullable()->after('tanggal_transfer');
            }
            if (!Schema::hasColumn('payment_proofs', 'nama_pengirim')) {
                $table->string('nama_pengirim', 100)->nullable()->after('bank_pengirim');
            }
        });

        // 2. payment_methods: dukung tipe & gambar QRIS
        Schema::table('payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_methods', 'type')) {
                $table->string('type', 20)->default('bank')->after('code'); // bank | ewallet | qris
            }
            if (!Schema::hasColumn('payment_methods', 'qris_image_path')) {
                $table->string('qris_image_path')->nullable()->after('icon');
            }
            if (!Schema::hasColumn('payment_methods', 'instructions')) {
                $table->text('instructions')->nullable()->after('qris_image_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->dropColumn(['student_id', 'nominal_transfer', 'tanggal_transfer', 'bank_pengirim', 'nama_pengirim']);
        });
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['type', 'qris_image_path', 'instructions']);
        });
    }
};
