<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'sort_by' => ['nullable', 'string', Rule::in(['id', 'name', 'price', 'created_at', 'updated_at'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'include_deleted' => ['nullable', 'boolean']
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'The status must be one of: active, inactive.',
            
            'sort_by.in' => 'The sort field must be one of: id, name, price, created_at, updated_at.',
            
            'sort_direction.in' => 'The sort direction must be either asc or desc.',
            
            'per_page.integer' => 'The per page value must be an integer.',
            'per_page.min' => 'The per page value must be at least 1.',
            'per_page.max' => 'The per page value may not be greater than 100.',
            
            'page.integer' => 'The page number must be an integer.',
            'page.min' => 'The page number must be at least 1.',
            
            'include_deleted.boolean' => 'The include deleted flag must be true or false.'
        ];
    }


    /**
     * Get the status filter.
     */
    public function getStatus(): ?string
    {
        return $this->input('status');
    }

    /**
     * Get the sort field.
     */
    public function getSortBy(): string
    {
        return $this->input('sort_by', 'created_at');
    }

    /**
     * Get the sort direction.
     */
    public function getSortDirection(): string
    {
        return $this->input('sort_direction', 'desc');
    }

    /**
     * Get the per page value.
     */
    public function getPerPage(): int
    {
        return $this->input('per_page', 15);
    }

    /**
     * Get the page number.
     */
    public function getPage(): int
    {
        return $this->input('page', 1);
    }

    /**
     * Check if include deleted is requested.
     */
    public function shouldIncludeDeleted(): bool
    {
        return $this->boolean('include_deleted', false);
    }

    /**
     * Get all applied filters as an array.
     */
    public function getFilters(): array
    {
        return [
            'status' => $this->getStatus(),
            'sort_by' => $this->getSortBy(),
            'sort_direction' => $this->getSortDirection(),
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
