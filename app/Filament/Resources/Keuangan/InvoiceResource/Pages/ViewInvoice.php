<?php

namespace App\Filament\Resources\Keuangan\InvoiceResource\Pages;

use App\Filament\Resources\Keuangan\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
