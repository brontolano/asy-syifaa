<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom `type` ke payment_proofs untuk membedakan:
 *  - 'invoice' : bukti bayar tagihan (default, perilaku lama)
 *  - 'topup'   : setoran saldo tabungan santri (invoice_id null)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_proofs', 'type')) {
                $table->string('type', 20)->default('invoice')->after('id'); // invoice | topup
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            if (Schema::hasColumn('payment_proofs', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
