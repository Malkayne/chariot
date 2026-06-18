<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_zone_id'    => ['required', 'exists:zones,id'],
            'to_zone_id'      => ['required', 'exists:zones,id', 'different:from_zone_id'],
            'available_seats' => ['required', 'integer', 'min:1', 'max:30'],
            'departing_at'    => ['nullable', 'date', 'after:now'],
            'notes'           => ['nullable', 'string', 'max:500'],
            'pickup_lat'      => ['nullable', 'numeric'],
            'pickup_lng'      => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_zone_id.different' => 'Destination must be different from pickup zone.',
            'departing_at.after'   => 'Departure time must be in the future.',
        ];
    }
}
