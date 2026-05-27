<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpmbRegisterRequest;
use App\Models\PpdbRegistration;
use App\Services\SpmbService;
use Illuminate\Http\JsonResponse;

class SpmbRegisterController extends Controller
{
    public function __construct(
        protected SpmbService $spmbService,
    ) {}

    public function register(SpmbRegisterRequest $request): JsonResponse
    {
        $registration = $this->spmbService->register($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil',
            'data' => [
                'registration_number' => $registration->registration_number,
                'username' => $registration->account?->username,
                'status' => $registration->status,
            ],
        ], 201);
    }

    public function status(string $registrationNumber): JsonResponse
    {
        $registration = PpdbRegistration::where('registration_number', $registrationNumber)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'registration_number' => $registration->registration_number,
                'student_name' => $registration->student_name,
                'status' => $registration->status,
                'document_status' => $registration->document_status,
            ],
        ]);
    }

    public function documents(string $registrationNumber): JsonResponse
    {
        $registration = PpdbRegistration::where('registration_number', $registrationNumber)->firstOrFail();
        $mandatoryDocs = config('spmb.mandatory_documents');

        $uploadedDocs = $registration->documents->keyBy('document_type');

        $checklist = collect($mandatoryDocs)->map(function ($label, $type) use ($uploadedDocs) {
            $doc = $uploadedDocs->get($type);

            return [
                'type' => $type,
                'label' => $label,
                'status' => $doc?->status ?? 'not_uploaded',
                'rejection_reason' => $doc?->rejection_reason,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $checklist,
        ]);
    }
}
