<?php

namespace App\Events;

use App\Models\Payment;
use App\Models\PpdbRegistration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DaftarUlangPaid
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PpdbRegistration $registration,
        public Payment $payment,
    ) {}
}
