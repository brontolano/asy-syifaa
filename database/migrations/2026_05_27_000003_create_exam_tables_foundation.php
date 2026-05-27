<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 9);
            $table->string('title');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_schedule_id')->constrained()->cascadeOnDelete();
            $table->text('question_text');
            $table->string('question_type', 10); // pg, esai
            $table->jsonb('options')->nullable();
            $table->string('correct_answer')->nullable();
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppdb_registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_schedule_id')->constrained()->cascadeOnDelete();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('auto_saved_at')->nullable();
            $table->decimal('total_score', 8, 2)->nullable();
            $table->boolean('is_graded')->default(false);
            $table->timestamps();
        });

        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_question_id')->constrained()->cascadeOnDelete();
            $table->text('answer_text')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->dateTime('graded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exam_schedules');
    }
};
