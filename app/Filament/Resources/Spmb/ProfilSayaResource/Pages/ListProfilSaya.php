<?php

namespace App\Filament\Resources\Spmb\ProfilSayaResource\Pages;

use App\Filament\Resources\Spmb\ProfilSayaResource;
use Filament\Resources\Pages\ListRecords;

class ListProfilSaya extends ListRecords
{
    protected static string $resource = ProfilSayaResource::class;

    public function mount(): void
    {
        parent::mount();

        // Auto-redirect to edit form if user has only 1 registration
        $user = auth('erp')->user();
        if ($user) {
            $registrations = $user->registrations;
            if ($registrations->count() === 1) {
                $this->redirect(ProfilSayaResource::getUrl('edit', ['record' => $registrations->first()->id]));
            }
        }
    }
}
