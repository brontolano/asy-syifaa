<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        // This seeder documents the mandatory document types
        // They are configured in config/spmb.php 'mandatory_documents'
        // No database table needed — config-driven approach

        $this->command->info('Mandatory documents configured in config/spmb.php:');
        foreach (config('spmb.mandatory_documents', []) as $key => $label) {
            $this->command->line("  - {$key}: {$label}");
        }
    }
}
