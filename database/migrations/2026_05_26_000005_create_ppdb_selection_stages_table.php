<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_selection_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppdb_registration_id')->constrained()->cascadeOnDelete();
            $table->enum('stage_name', [
                'admin_check', 'interview', 'quran_test', 'health_check',
            ]);
            $table->enum('result', ['pending', 'pass', 'fail'])->default('pending');
            $table->foreignId('examiner_id')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('conducted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_selection_stages');
    }
};
