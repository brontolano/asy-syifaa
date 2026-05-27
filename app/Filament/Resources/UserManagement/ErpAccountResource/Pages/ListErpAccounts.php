<?php

namespace App\Filament\Resources\UserManagement\ErpAccountResource\Pages;

use App\Filament\Resources\UserManagement\ErpAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListErpAccounts extends ListRecords
{
    protected static string $resource = ErpAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Akun'),
        ];
    }
}
