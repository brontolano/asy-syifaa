<?php

namespace App\Filament\Resources\Spmb\ProfilSayaResource\Pages;

use App\Filament\Resources\Spmb\ProfilSayaResource;
use Filament\Resources\Pages\EditRecord;

class EditProfilSaya extends EditRecord
{
    protected static string $resource = ProfilSayaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
