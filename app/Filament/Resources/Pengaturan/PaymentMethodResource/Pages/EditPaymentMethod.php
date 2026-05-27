<?php

namespace App\Filament\Resources\Pengaturan\PaymentMethodResource\Pages;

use App\Filament\Resources\Pengaturan\PaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentMethod extends EditRecord
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
