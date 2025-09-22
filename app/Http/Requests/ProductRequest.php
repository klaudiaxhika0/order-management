<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->getKey();

        return [
            'name' => [
                $productId ? 'sometimes' : 'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_&.,()]+$/',
                Rule::unique('products', 'name')->ignore($productId),
            ],
            'description' => [
                'nullable', 
                'string', 
                'min:10',
                'max:2000'
            ],
            'price' => [
                $productId ? 'sometimes' : 'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'sku' => [
                $productId ? 'sometimes' : 'nullable',
                'string',
                'min:3',
                'max:50',
                'regex:/^[A-Z0-9\-_]+$/',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'weight' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999.99'
            ],
            'dimensions' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^\d+x\d+x\d+$/'
            ],
            'tags' => [
                'nullable',
                'array',
                'max:10'
            ],
            'tags.*' => [
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-_]+$/'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            
            'name.required' => 'Product name is required.',
            'name.min' => 'Product name must be at least 2 characters.',
            'name.max' => 'Product name may not be greater than 255 characters.',
            'name.regex' => 'Product name may only contain letters, numbers, spaces, hyphens, underscores, ampersands, periods, commas, and parentheses.',
            'name.unique' => 'This product name is already in use.',
            
            'description.min' => 'Description must be at least 10 characters.',
            'description.max' => 'Description may not be greater than 2000 characters.',
            
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least 0.01.',
            'price.max' => 'Price may not be greater than 999,999.99.',
            'price.regex' => 'Price must be in valid format (e.g., 10.99).',
            
            
            'sku.min' => 'SKU must be at least 3 characters.',
            'sku.max' => 'SKU may not be greater than 50 characters.',
            'sku.regex' => 'SKU may only contain uppercase letters, numbers, hyphens, and underscores.',
            'sku.unique' => 'This SKU is already in use.',
            
            'status.in' => 'Status must be one of: active, inactive.',
            
            'weight.numeric' => 'Weight must be a number.',
            'weight.min' => 'Weight must be at least 0.',
            'weight.max' => 'Weight may not be greater than 999.99.',
            
            'dimensions.max' => 'Dimensions may not be greater than 100 characters.',
            'dimensions.regex' => 'Dimensions must be in format: length x width x height (e.g., 10x5x3).',
            
            'tags.array' => 'Tags must be an array.',
            'tags.max' => 'You may not have more than 10 tags.',
            'tags.*.string' => 'Each tag must be a string.',
            'tags.*.max' => 'Each tag may not be greater than 50 characters.',
            'tags.*.regex' => 'Each tag may only contain letters, numbers, spaces, hyphens, and underscores.',
        ];
    }


    /**
     * Get the product name.
     */
    public function getName(): ?string
    {
        return $this->input('name');
    }

    /**
     * Get the product description.
     */
    public function getDescription(): ?string
    {
        return $this->input('description');
    }

    /**
     * Get the product price.
     */
    public function getPrice(): ?float
    {
        return $this->input('price');
    }


    /**
     * Get the product SKU.
     */
    public function getSku(): ?string
    {
        return $this->input('sku');
    }

    /**
     * Get the product status.
     */
    public function getStatus(): ?string
    {
        return $this->input('status');
    }

    /**
     * Get the product weight.
     */
    public function getWeight(): ?float
    {
        return $this->input('weight');
    }

    /**
     * Get the product dimensions.
     */
    public function getDimensions(): ?string
    {
        return $this->input('dimensions');
    }

    /**
     * Get the product tags.
     */
    public function getTags(): ?array
    {
        return $this->input('tags');
    }

    /**
     * Check if this is an update request.
     */
    public function isUpdate(): bool
    {
        return $this->route('product') !== null;
    }

    /**
     * Get the product ID for updates.
     */
    public function getProductId(): ?int
    {
        return $this->route('product')?->getKey();
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'errors'  => $validator->errors(),
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
