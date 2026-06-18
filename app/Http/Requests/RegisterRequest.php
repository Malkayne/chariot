<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:100'],
            'phone'          => ['required', 'string', 'max:20', 'unique:users,phone'],
            'email'          => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password'       => ['required', 'string', 'min:6', 'confirmed'],
            'role'           => ['required', 'in:rider,driver'],
            'rccg_member_id' => ['nullable', 'string', 'max:60'],
            // Driver-only fields
            'vehicle_type'   => ['required_if:role,driver', 'nullable', 'string', 'max:30'],
            'vehicle_model'  => ['required_if:role,driver', 'nullable', 'string', 'max:80'],
            'vehicle_color'  => ['required_if:role,driver', 'nullable', 'string', 'max:40'],
            'plate_number'   => ['required_if:role,driver', 'nullable', 'string', 'max:20', 'unique:driver_profiles,plate_number'],
            'total_seats'    => ['required_if:role,driver', 'nullable', 'integer', 'min:1', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique'        => 'This phone number is already registered.',
            'email.unique'        => 'This email address is already registered.',
            'plate_number.unique' => 'This plate number is already registered.',
            'vehicle_type.required_if'  => 'Vehicle type is required for drivers.',
            'vehicle_model.required_if' => 'Vehicle model is required for drivers.',
            'vehicle_color.required_if' => 'Vehicle colour is required for drivers.',
            'plate_number.required_if'  => 'Plate number is required for drivers.',
            'total_seats.required_if'   => 'Number of seats is required for drivers.',
        ];
    }
}
