<?php

namespace App\Filament\Resources\Cms\CmsCategoryResource\Pages;

use App\Filament\Resources\Cms\CmsCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCmsCategory extends EditRecord
{
    protected static string $resource = CmsCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
