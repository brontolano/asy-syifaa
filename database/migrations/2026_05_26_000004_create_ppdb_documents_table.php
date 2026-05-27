<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppdb_registration_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', [
                'ijazah', 'akta_kelahiran', 'kartu_keluarga',
                'foto', 'surat_kesehatan', 'rapor', 'lainnya',
            ]);
            $table->string('file_path');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_documents');
    }
};
