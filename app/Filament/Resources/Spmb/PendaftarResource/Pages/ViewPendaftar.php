<?php

namespace App\Filament\Resources\Spmb\PendaftarResource\Pages;

use App\Filament\Resources\Spmb\PendaftarResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPendaftar extends ViewRecord
{
    protected static string $resource = PendaftarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
