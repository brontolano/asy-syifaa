<?php

namespace App\Filament\Resources\Notifikasi\NotificationTemplateResource\Pages;

use App\Filament\Resources\Notifikasi\NotificationTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotificationTemplates extends ListRecords
{
    protected static string $resource = NotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
