<?php

namespace App\Filament\Resources\UserManagement\ErpAccountResource\Pages;

use App\Filament\Resources\UserManagement\ErpAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditErpAccount extends EditRecord
{
    protected static string $resource = ErpAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
