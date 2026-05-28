<?php

namespace App\Filament\Resources\Notifikasi\BroadcastJobResource\Pages;

use App\Filament\Resources\Notifikasi\BroadcastJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBroadcastJobs extends ListRecords
{
    protected static string $resource = BroadcastJobResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
