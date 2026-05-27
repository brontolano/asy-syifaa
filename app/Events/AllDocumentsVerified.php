<?php

namespace App\Events;

use App\Models\PpdbRegistration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AllDocumentsVerified
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PpdbRegistration $registration,
    ) {}
}
