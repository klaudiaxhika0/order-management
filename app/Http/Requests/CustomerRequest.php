<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
        $customerId = $this->route('customer')?->getKey();

        return [
            'first_name' => [
                'required', 
                'string', 
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s\-\']+$/'
            ],
            'last_name' => [
                'required', 
                'string', 
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s\-\']+$/'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'phone' => [
                'nullable', 
                'string', 
                'min:10',
                'max:20',
                'regex:/^\+[1-9]\d{0,15}$/'
            ],
            'address' => [
                'nullable', 
                'string', 
                'min:10',
                'max:500'
            ],
            'city' => [
                'nullable',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s\-\']+$/'
            ],
            'state' => [
                'nullable',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s\-\']+$/'
            ],
            'postal_code' => [
                'nullable',
                'string',
                'min:5',
                'max:20',
                'regex:/^[a-zA-Z0-9\s\-]+$/'
            ],
            'country' => [
                'nullable',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s\-\']+$/'
            ],
            'date_of_birth' => [
                'nullable',
                'date',
                'before:today',
                'after:1900-01-01'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'first_name.max' => 'First name may not be greater than 100 characters.',
            'first_name.regex' => 'First name may only contain letters, spaces, hyphens, and apostrophes.',
            
            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'last_name.max' => 'Last name may not be greater than 100 characters.',
            'last_name.regex' => 'Last name may only contain letters, spaces, hyphens, and apostrophes.',
            
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email address.',
            'email.max' => 'Email may not be greater than 255 characters.',
            'email.unique' => 'This email is already in use.',
            
            'phone.min' => 'Phone number must be at least 10 characters.',
            'phone.max' => 'Phone number may not be greater than 20 characters.',
            'phone.regex' => 'Phone number must start with + followed by numbers only (e.g., +1234567890).',
            
            'address.min' => 'Address must be at least 10 characters.',
            'address.max' => 'Address may not be greater than 500 characters.',
            
            'city.min' => 'City must be at least 2 characters.',
            'city.max' => 'City may not be greater than 100 characters.',
            'city.regex' => 'City may only contain letters, spaces, hyphens, and apostrophes.',
            
            'state.min' => 'State must be at least 2 characters.',
            'state.max' => 'State may not be greater than 100 characters.',
            'state.regex' => 'State may only contain letters, spaces, hyphens, and apostrophes.',
            
            'postal_code.min' => 'Postal code must be at least 5 characters.',
            'postal_code.max' => 'Postal code may not be greater than 20 characters.',
            'postal_code.regex' => 'Postal code may only contain letters, numbers, spaces, and hyphens.',
            
            'country.min' => 'Country must be at least 2 characters.',
            'country.max' => 'Country may not be greater than 100 characters.',
            'country.regex' => 'Country may only contain letters, spaces, hyphens, and apostrophes.',
            
            'date_of_birth.date' => 'Date of birth must be a valid date.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'date_of_birth.after' => 'Date of birth must be after 1900-01-01.',
            
            'notes.max' => 'Notes may not be greater than 1000 characters.'
        ];
    }


    /**
     * Get the first name.
     */
    public function getFirstName(): string
    {
        return $this->input('first_name');
    }

    /**
     * Get the last name.
     */
    public function getLastName(): string
    {
        return $this->input('last_name');
    }

    /**
     * Get the full name.
     */
    public function getFullName(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * Get the email.
     */
    public function getEmail(): string
    {
        return $this->input('email');
    }

    /**
     * Get the phone number.
     */
    public function getPhone(): ?string
    {
        return $this->input('phone');
    }

    /**
     * Get the address.
     */
    public function getAddress(): ?string
    {
        return $this->input('address');
    }

    /**
     * Get the city.
     */
    public function getCity(): ?string
    {
        return $this->input('city');
    }

    /**
     * Get the state.
     */
    public function getState(): ?string
    {
        return $this->input('state');
    }

    /**
     * Get the postal code.
     */
    public function getPostalCode(): ?string
    {
        return $this->input('postal_code');
    }

    /**
     * Get the country.
     */
    public function getCountry(): ?string
    {
        return $this->input('country');
    }

    /**
     * Get the date of birth.
     */
    public function getDateOfBirth(): ?string
    {
        return $this->input('date_of_birth');
    }

    /**
     * Get the notes.
     */
    public function getNotes(): ?string
    {
        return $this->input('notes');
    }

    /**
     * Check if this is an update request.
     */
    public function isUpdate(): bool
    {
        return $this->route('customers') !== null;
    }

    /**
     * Get the customer ID for updates.
     */
    public function getCustomerId(): ?int
    {
        return $this->route('customers')?->getKey();
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
