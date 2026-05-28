<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('channel')->default('whatsapp'); // whatsapp, email, sms
            $table->string('subject')->nullable();
            $table->longText('body_template');
            $table->json('variables')->nullable(); // e.g. ["name","phone","registration_number"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $table->string('channel');
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->json('payload')->nullable();
            $table->string('status')->default('pending'); // pending, sent, failed, delivered
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->nullableMorphs('notifiable');
            $table->timestamps();
        });

        Schema::create('broadcast_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $table->string('channel')->default('whatsapp');
            $table->json('filter_criteria')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->string('status')->default('draft'); // draft, processing, completed, cancelled
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_jobs');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_templates');
    }
};
