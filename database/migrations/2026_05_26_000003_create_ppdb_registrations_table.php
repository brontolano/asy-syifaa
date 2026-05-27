<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number', 30)->unique();
            $table->string('academic_year', 9);
            $table->string('student_name');
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->enum('gender', ['L', 'P']);
            $table->string('nik', 16)->nullable();
            $table->string('origin_school')->nullable();
            $table->string('parent_name');
            $table->string('parent_phone', 20);
            $table->string('parent_email')->nullable();
            $table->text('address')->nullable();
            $table->enum('status', [
                'pending', 'document_review', 'selection',
                'accepted', 'rejected', 'enrolled',
            ])->default('pending');
            $table->enum('source', ['website', 'manual'])->default('manual');
            $table->string('external_ref_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('academic_year');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_registrations');
    }
};
