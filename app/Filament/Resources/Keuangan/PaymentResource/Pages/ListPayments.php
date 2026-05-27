<?php

namespace App\Filament\Resources\Keuangan\PaymentResource\Pages;

use App\Filament\Resources\Keuangan\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Catat Pembayaran'),
        ];
    }
}
