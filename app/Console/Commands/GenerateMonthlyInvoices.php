<?php

namespace App\Console\Commands;

use App\Services\KeuanganService;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'keuangan:generate-spp {--check-overdue : Also mark overdue invoices} {--check-waqof : Auto-waqof students with 3+ months tunggakan}';
    protected $description = 'Generate monthly SPP invoices for current Hijri period and maintain billing';

    public function handle(): int
    {
        $service = new KeuanganService();

        // Generate SPP
        $period = $service->getCurrentHijriPeriod();
        if ($period) {
            $this->info("Current period: {$period->label}");
            $created = $service->generateMonthlyInvoices($period);
            $this->info("SPP invoices created: {$created}");
        } else {
            $this->warn('No Hijri billing period found for current date.');
        }

        // Mark overdue
        if ($this->option('check-overdue')) {
            $overdue = $service->markOverdueInvoices();
            $this->info("Marked {$overdue} invoices as overdue.");
        }

        // Check waqof
        if ($this->option('check-waqof')) {
            $waqofed = $service->checkAndApplyWaqof();
            $this->info("Auto-waqof: " . count($waqofed) . " students.");
            foreach ($waqofed as $s) {
                $this->warn("  - {$s->nis} {$s->full_name} ({$s->tunggakan_bulan} bulan tunggakan)");
            }
        }

        return 0;
    }
}
