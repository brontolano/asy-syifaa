<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpmbRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:20', 'unique:ppdb_registrations,nik'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['nullable', 'date'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['required', 'string', 'max:20'],
            'parent_email' => ['nullable', 'email', 'max:255'],
            'academic_year' => ['required', 'string', 'max:10'],
            'source' => ['nullable', 'string', 'in:website,manual,referral'],
        ];
    }
}
