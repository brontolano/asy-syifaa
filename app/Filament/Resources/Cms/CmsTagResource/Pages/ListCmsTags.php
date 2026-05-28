<?php

namespace App\Filament\Resources\Cms\CmsTagResource\Pages;

use App\Filament\Resources\Cms\CmsTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCmsTags extends ListRecords
{
    protected static string $resource = CmsTagResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
