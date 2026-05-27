<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('academic_year', 9);
            $table->enum('semester', ['ganjil', 'genap']);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_periods');
    }
};
