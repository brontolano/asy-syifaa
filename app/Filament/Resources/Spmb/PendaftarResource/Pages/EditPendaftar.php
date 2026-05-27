<?php

namespace App\Filament\Resources\Spmb\PendaftarResource\Pages;

use App\Filament\Resources\Spmb\PendaftarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendaftar extends EditRecord
{
    protected static string $resource = PendaftarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
