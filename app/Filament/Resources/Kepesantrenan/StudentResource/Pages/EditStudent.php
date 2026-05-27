<?php

namespace App\Filament\Resources\Kepesantrenan\StudentResource\Pages;

use App\Filament\Resources\Kepesantrenan\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
