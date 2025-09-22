<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_id' => [
                'required',
                'integer',
                'exists:order_statuses,id'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'status_id.required' => 'Order status is required.',
            'status_id.integer' => 'Status ID must be an integer.',
            'status_id.exists' => 'Selected status does not exist.'
        ];
    }
}