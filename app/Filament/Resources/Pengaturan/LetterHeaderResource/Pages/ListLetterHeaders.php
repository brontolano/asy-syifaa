<?php

namespace App\Filament\Resources\Pengaturan\LetterHeaderResource\Pages;

use App\Filament\Resources\Pengaturan\LetterHeaderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterHeaders extends ListRecords
{
    protected static string $resource = LetterHeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
