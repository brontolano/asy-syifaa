<?php

namespace App\Filament\Resources\Notifikasi\NotificationTemplateResource\Pages;

use App\Filament\Resources\Notifikasi\NotificationTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotificationTemplate extends EditRecord
{
    protected static string $resource = NotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
