<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'wali_account_id')) {
                $table->foreignId('wali_account_id')->nullable()->after('erp_account_id')
                    ->constrained('erp_accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('students', 'enrolled_from_ppdb_id')) {
                $table->foreignId('enrolled_from_ppdb_id')->nullable()->after('ppdb_registration_id')
                    ->constrained('ppdb_registrations')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wali_account_id');
            $table->dropConstrainedForeignId('enrolled_from_ppdb_id');
        });
    }
};
