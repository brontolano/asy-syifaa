<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enhance ppdb_registrations
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->foreignId('erp_account_id')->nullable()->after('id')
                ->constrained('erp_accounts')->nullOnDelete();
            $table->string('document_status', 30)->default('incomplete')->after('status');
            $table->timestamp('profile_completed_at')->nullable()->after('notes');
        });

        // Enhance ppdb_documents
        Schema::table('ppdb_documents', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('file_path');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->unsignedInteger('version')->default(1)->after('rejection_reason');
            $table->index(['ppdb_registration_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_documents', function (Blueprint $table) {
            $table->dropIndex(['ppdb_registration_id', 'document_type']);
            $table->dropColumn(['status', 'rejection_reason', 'version']);
        });

        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('erp_account_id');
            $table->dropColumn(['document_status', 'profile_completed_at']);
        });
    }
};
