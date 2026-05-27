<?php

namespace App\Filament\Resources\Keuangan\PaymentResource\Pages;

use App\Filament\Resources\Keuangan\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['received_by'] = auth()->id();

        return $data;
    }
}
