<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->string('parent_job')->nullable()->after('parent_email');
            $table->text('parent_address')->nullable()->after('parent_job');
            $table->string('guardian_name')->nullable()->after('parent_address');
            $table->string('guardian_phone')->nullable()->after('guardian_name');
            $table->string('guardian_relation')->nullable()->after('guardian_phone');
            $table->text('guardian_address')->nullable()->after('guardian_relation');
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->dropColumn(['parent_job', 'parent_address', 'guardian_name', 'guardian_phone', 'guardian_relation', 'guardian_address']);
        });
    }
};
