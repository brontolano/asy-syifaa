<?php

namespace App\Filament\Resources\Kepesantrenan\StudentResource\Pages;

use App\Filament\Resources\Kepesantrenan\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
