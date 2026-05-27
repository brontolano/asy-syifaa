<?php

namespace App\Filament\Resources\Keuangan\BillingTypeResource\Pages;

use App\Filament\Resources\Keuangan\BillingTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBillingType extends EditRecord
{
    protected static string $resource = BillingTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
