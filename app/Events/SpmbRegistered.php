<?php

namespace App\Events;

use App\Models\PpdbRegistration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SpmbRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PpdbRegistration $registration,
        public string $plainPassword,
    ) {}
}
