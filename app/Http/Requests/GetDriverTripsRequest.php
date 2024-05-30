<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GetDriverTripsRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'driver_id' => 'sometimes|integer',
            'pickup' => 'sometimes|date',
            'dropoff' => 'sometimes|date',
            'sort_by' => 'sometimes|string|in:driver_id,pickup,dropoff',
            'sort_order' => 'sometimes|string|in:asc,desc',
        ];
    }
}
