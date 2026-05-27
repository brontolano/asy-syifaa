<?php

namespace App\Events;

use App\Models\PpdbDocument;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentVerified
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PpdbDocument $document,
        public string $action, // 'approved' or 'rejected'
    ) {}
}
