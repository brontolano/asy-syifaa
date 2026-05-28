<?php

namespace App\Filament\Resources\Cms\CmsGalleryResource\Pages;

use App\Filament\Resources\Cms\CmsGalleryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCmsGalleries extends ListRecords
{
    protected static string $resource = CmsGalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
