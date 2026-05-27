<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PpdbRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpmbSyncController extends Controller
{
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_name' => 'required|string|max:255',
            'academic_year' => 'required|string|max:9',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string',
            'gender' => 'required|in:L,P',
            'nik' => 'nullable|string|max:16',
            'origin_school' => 'nullable|string',
            'parent_name' => 'required|string',
            'parent_phone' => 'required|string|max:20',
            'parent_email' => 'nullable|email',
            'address' => 'nullable|string',
            'external_ref_id' => 'nullable|string',
        ]);

        $validated['source'] = 'website';
        $validated['status'] = 'pending';

        $registration = PpdbRegistration::create($validated);

        return response()->json([
            'success' => true,
            'registration_number' => $registration->registration_number,
            'id' => $registration->id,
        ], 201);
    }

    public function status(string $externalRefId): JsonResponse
    {
        $registration = PpdbRegistration::where('external_ref_id', $externalRefId)->first();

        if (! $registration) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        return response()->json([
            'success' => true,
            'registration_number' => $registration->registration_number,
            'status' => $registration->status,
            'student_name' => $registration->student_name,
        ]);
    }
}
