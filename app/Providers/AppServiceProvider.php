<?php

namespace App\Providers;

use App\Events\AllDocumentsVerified;
use App\Events\DaftarUlangPaid;
use App\Events\DocumentVerified;
use App\Events\PaymentProofApproved;
use App\Events\SelectionDecided;
use App\Events\SpmbRegistered;
use App\Listeners\ConvertPendaftarToSantri;
use App\Listeners\HandlePaymentProofApproved;
use App\Listeners\HandleSelectionDecision;
use App\Listeners\SendAllDocsVerifiedNotification;
use App\Listeners\SendDocumentRejectedNotification;
use App\Listeners\SendRegistrationWebhook;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme('https');

        Event::listen(SpmbRegistered::class, SendRegistrationWebhook::class);
        Event::listen(DocumentVerified::class, SendDocumentRejectedNotification::class);
        Event::listen(AllDocumentsVerified::class, SendAllDocsVerifiedNotification::class);
        Event::listen(SelectionDecided::class, HandleSelectionDecision::class);
        Event::listen(DaftarUlangPaid::class, ConvertPendaftarToSantri::class);
        Event::listen(PaymentProofApproved::class, HandlePaymentProofApproved::class);
    }
}
