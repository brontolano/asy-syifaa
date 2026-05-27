<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();
            $table->string('student_name');
            $table->string('student_id')->nullable();
            $table->foreignId('billing_period_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('status', [
                'draft', 'issued', 'partial', 'paid', 'overdue', 'cancelled',
            ])->default('draft');
            $table->date('due_date')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
