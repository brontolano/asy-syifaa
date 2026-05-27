<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE ppdb_documents DROP CONSTRAINT IF EXISTS ppdb_documents_document_type_check');

        DB::statement("ALTER TABLE ppdb_documents ADD CONSTRAINT ppdb_documents_document_type_check CHECK (document_type IN ('ijazah', 'akta_kelahiran', 'kartu_keluarga', 'foto_3x4', 'foto_4x6', 'surat_kesehatan', 'rapor_terakhir', 'surat_rekomendasi', 'foto', 'rapor', 'lainnya'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE ppdb_documents DROP CONSTRAINT IF EXISTS ppdb_documents_document_type_check');

        DB::statement("ALTER TABLE ppdb_documents ADD CONSTRAINT ppdb_documents_document_type_check CHECK (document_type IN ('ijazah', 'akta_kelahiran', 'kartu_keluarga', 'foto', 'surat_kesehatan', 'rapor', 'lainnya'))");
    }
};
