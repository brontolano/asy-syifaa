<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Letter header templates for surat
        Schema::create('letter_headers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Template name');
            $table->string('institution_name')->default('Pondok Pesantren Asy-Syifaa Wal Mahmuudiyyah');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('secondary_logo_path')->nullable();
            $table->text('tagline')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->jsonb('extra_fields')->nullable()->comment('Additional custom fields');
            $table->timestamps();
        });

        // 2. Surat/document templates
        Schema::create('surat_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('category', 30)->default('umum')->comment('keuangan, kepesantrenan, akademik, umum');
            $table->foreignId('letter_header_id')->nullable()->constrained('letter_headers')->nullOnDelete();
            $table->text('body_template')->comment('Blade-compatible template body');
            $table->string('paper_size', 10)->default('A4');
            $table->string('orientation', 10)->default('portrait');
            $table->jsonb('available_variables')->nullable()->comment('List of merge fields');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Payment proof images (for staff upload bukti transfer)
        if (!Schema::hasColumn('payments', 'proof_image')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('proof_image')->nullable()->after('notes')->comment('Path to transfer proof image');
            });
        }

        // 4. Enhance students — add more finance/notes fields
        Schema::table('students', function (Blueprint $table) {
            $table->decimal('adm_amount', 12, 2)->default(0)->after('spp_amount')->comment('Biaya admin/pendaftaran');
            $table->decimal('ujian_amount', 12, 2)->default(0)->after('adm_amount')->comment('Biaya ujian');
            $table->text('catatan_kesehatan')->nullable()->after('waqof_reason');
            $table->text('catatan_kedisiplinan')->nullable()->after('catatan_kesehatan');
            $table->text('catatan_umum')->nullable()->after('catatan_kedisiplinan');
        });

        // 5. App settings table
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 30)->default('general');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string')->comment('string, integer, boolean, json, file');
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('proof_image');
        });
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['adm_amount', 'ujian_amount', 'catatan_kesehatan', 'catatan_kedisiplinan', 'catatan_umum']);
        });
        Schema::dropIfExists('surat_templates');
        Schema::dropIfExists('letter_headers');
        Schema::dropIfExists('app_settings');
    }
};
