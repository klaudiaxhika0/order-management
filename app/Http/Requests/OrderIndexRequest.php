<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'status_id' => ['nullable', 'integer', 'exists:order_statuses,id'],
            'created_from' => ['nullable', 'date', 'before_or_equal:created_to'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
            'sort_by' => ['nullable', 'string', Rule::in(['id', 'total', 'created_at', 'updated_at'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'include_deleted' => ['nullable', 'boolean']
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.integer' => 'The customer ID must be an integer.',
            'customer_id.exists' => 'The selected customer does not exist.',
            
            'status_id.integer' => 'The status ID must be an integer.',
            'status_id.exists' => 'The selected status does not exist.',
            
            'created_from.date' => 'The created from date must be a valid date.',
            'created_from.before_or_equal' => 'The created from date must be before or equal to the created to date.',
            
            'created_to.date' => 'The created to date must be a valid date.',
            'created_to.after_or_equal' => 'The created to date must be after or equal to the created from date.',
            
            'sort_by.in' => 'The sort field must be one of: id, total, created_at, updated_at.',
            
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
     * Get the customer ID filter.
     */
    public function getCustomerId(): ?int
    {
        return $this->input('customer_id');
    }

    /**
     * Get the status ID filter.
     */
    public function getStatusId(): ?int
    {
        return $this->input('status_id');
    }

    /**
     * Get the created from date filter.
     */
    public function getCreatedFrom(): ?string
    {
        return $this->input('created_from');
    }

    /**
     * Get the created to date filter.
     */
    public function getCreatedTo(): ?string
    {
        return $this->input('created_to');
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
            'customer_id' => $this->getCustomerId(),
            'status_id' => $this->getStatusId(),
            'created_from' => $this->getCreatedFrom(),
            'created_to' => $this->getCreatedTo(),
            'sort_by' => $this->getSortBy(),
            'sort_direction' => $this->getSortDirection(),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
