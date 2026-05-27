<?php

namespace App\Filament\Resources\Spmb\PendaftarResource\Pages;

use App\Filament\Resources\Spmb\PendaftarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendaftar extends ListRecords
{
    protected static string $resource = PendaftarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Pendaftar'),
        ];
    }
}
