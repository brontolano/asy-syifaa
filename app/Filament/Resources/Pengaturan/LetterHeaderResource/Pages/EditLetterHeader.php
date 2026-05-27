<?php

namespace App\Filament\Resources\Pengaturan\LetterHeaderResource\Pages;

use App\Filament\Resources\Pengaturan\LetterHeaderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLetterHeader extends EditRecord
{
    protected static string $resource = LetterHeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
