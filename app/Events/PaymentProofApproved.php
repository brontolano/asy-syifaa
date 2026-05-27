<?php

namespace App\Events;

use App\Models\PaymentProof;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentProofApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PaymentProof $paymentProof,
    ) {}
}
