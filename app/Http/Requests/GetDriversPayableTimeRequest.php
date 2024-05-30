<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetDriversPayableTimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'driver_id' => 'sometimes|integer',
            'total_minutes_with_passenger' => 'sometimes|integer',
            'sort_by' => 'sometimes|string|in:driver_id,pickup,dropoff',
            'sort_order' => 'sometimes|string|in:asc,desc',
        ];
    }
}
