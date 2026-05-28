<?php

namespace App\Filament\Resources\Cms\CmsPostResource\Pages;

use App\Filament\Resources\Cms\CmsPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCmsPosts extends ListRecords
{
    protected static string $resource = CmsPostResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
