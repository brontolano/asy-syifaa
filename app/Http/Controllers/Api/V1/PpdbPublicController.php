<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PpdbRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PpdbPublicController extends Controller
{
    public function status(string $registrationNumber): JsonResponse
    {
        $reg = PpdbRegistration::where('registration_number', $registrationNumber)
            ->select(['id', 'registration_number', 'student_name', 'status', 'academic_year', 'created_at'])
            ->first();

        if (!$reg) {
            return response()->json([
                'ok' => false,
                'message' => 'Nomor pendaftaran tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => $reg,
        ]);
    }

    public function selectionResults(Request $request): JsonResponse
    {
        $query = PpdbRegistration::whereIn('status', ['accepted', 'waiting_list', 'rejected'])
            ->select(['registration_number', 'student_name', 'status', 'academic_year']);

        if ($request->filled('year')) {
            $query->where('academic_year', $request->year);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'ilike', "%{$search}%")
                    ->orWhere('registration_number', 'ilike', "%{$search}%");
            });
        }

        $results = $query->orderBy('student_name')->paginate(20);

        return response()->json([
            'ok' => true,
            'data' => $results->items(),
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'total' => $results->total(),
            ],
        ]);
    }
}
