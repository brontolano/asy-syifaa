<?php

namespace App\Services;

use App\Events\AllDocumentsVerified;
use App\Events\DaftarUlangPaid;
use App\Events\DocumentVerified;
use App\Events\SelectionDecided;
use App\Events\SpmbRegistered;
use App\Models\BillingType;
use App\Models\ErpAccount;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PpdbDocument;
use App\Models\PpdbRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SpmbService
{
    public function register(array $data): PpdbRegistration
    {
        return DB::transaction(function () use ($data) {
            $registration = PpdbRegistration::create(array_merge($data, [
                'status' => 'pending',
                'source' => $data['source'] ?? 'website',
                'document_status' => 'incomplete',
            ]));

            $plainPassword = $this->createAccountForRegistration($registration);

            event(new SpmbRegistered($registration, $plainPassword));

            return $registration;
        });
    }

    public function createAccountForRegistration(PpdbRegistration $registration): string
    {
        // Check if account with same phone already exists (1 akun bisa multi santri)
        $existingAccount = ErpAccount::where('phone', $registration->parent_phone)->first();

        if ($existingAccount) {
            // Link registration to existing account
            $registration->update(['erp_account_id' => $existingAccount->id]);

            // Generate new password for notification purposes
            $plainPassword = $this->generateRandomPassword();
            $existingAccount->update([
                'password' => $plainPassword,
                'must_change_password' => true,
            ]);

            if (!$existingAccount->hasRole('Pendaftar')) {
                $existingAccount->assignRole('Pendaftar');
            }

            return $plainPassword;
        }

        $username = $this->generateUsername($registration->student_name);
        $plainPassword = $this->generateRandomPassword();

        $account = ErpAccount::create([
            'username' => $username,
            'full_name' => $registration->parent_name ?? $registration->student_name,
            'email' => $registration->parent_email,
            'phone' => $registration->parent_phone,
            'password' => $plainPassword, // auto-hashed by cast
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $account->assignRole('Pendaftar');

        $registration->update(['erp_account_id' => $account->id]);

        return $plainPassword;
    }

    public function resetAndSendCredential(PpdbRegistration $registration): string
    {
        $account = $registration->account;
        if (!$account) {
            return $this->createAccountForRegistration($registration);
        }

        $plainPassword = $this->generateRandomPassword();
        $account->update([
            'password' => $plainPassword,
            'must_change_password' => true,
        ]);

        return $plainPassword;
    }

    public function verifyDocument(PpdbDocument $document, ErpAccount $staff, string $action, ?string $reason = null): void
    {
        $document->update([
            'status' => $action, // 'approved' or 'rejected'
            'rejection_reason' => $action === 'rejected' ? $reason : null,
            'verified_at' => now(),
            'verified_by' => $staff->id,
        ]);

        event(new DocumentVerified($document, $action));

        // Check if all mandatory documents are now approved
        $registration = $document->registration;
        if ($this->checkAllDocumentsApproved($registration)) {
            $registration->update(['document_status' => 'complete']);
            event(new AllDocumentsVerified($registration));
        } elseif ($action === 'rejected') {
            $registration->update(['document_status' => 'revision_needed']);
        } else {
            $registration->update(['document_status' => 'pending_review']);
        }
    }

    public function checkAllDocumentsApproved(PpdbRegistration $registration): bool
    {
        $mandatoryTypes = array_keys(config('spmb.mandatory_documents'));
        $approvedTypes = $registration->documents()
            ->where('status', 'approved')
            ->pluck('document_type')
            ->toArray();

        return empty(array_diff($mandatoryTypes, $approvedTypes));
    }

    public function setSelectionResult(PpdbRegistration $registration, string $result, ?string $notes = null): void
    {
        $registration->update([
            'status' => $result, // lulus, cadangan, rejected (tidak lulus)
            'notes' => $notes,
        ]);

        event(new SelectionDecided($registration, $result));
    }

    public function generateDaftarUlangInvoice(PpdbRegistration $registration): Invoice
    {
        $billingType = BillingType::where('code', 'DAFTAR')->first();

        $invoice = Invoice::create([
            'student_name' => $registration->student_name,
            'student_id' => $registration->registration_number,
            'status' => 'issued',
            'issued_at' => now(),
            'due_date' => now()->addDays(14),
        ]);

        if ($billingType) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'billing_type_id' => $billingType->id,
                'description' => 'Biaya Daftar Ulang - ' . $registration->student_name,
                'amount' => $billingType->amount_default,
            ]);
        }

        return $invoice->fresh();
    }

    public function convertToSantri(PpdbRegistration $registration): void
    {
        $account = $registration->account;
        if ($account) {
            $account->removeRole('Pendaftar');
            $account->assignRole('Santri');
        }

        $registration->update(['status' => 'enrolled']);
    }

    protected function generateUsername(string $fullName): string
    {
        $slug = Str::slug($fullName, '.');
        $slug = Str::limit($slug, 20, '');
        $random = rand(1000, 9999);

        $username = "{$slug}.{$random}";

        // Ensure uniqueness
        while (ErpAccount::where('username', $username)->exists()) {
            $random = rand(1000, 9999);
            $username = "{$slug}.{$random}";
        }

        return $username;
    }

    protected function generateRandomPassword(int $length = 10): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}
