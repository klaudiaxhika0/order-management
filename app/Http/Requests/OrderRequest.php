<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $order = $this->route('order');
        $orderId = $order ? (is_object($order) ? $order->getKey() : $order) : null;

        return [
            'customer_id' => [
                'required', 
                'integer', 
                'exists:customers,id'
            ],
            'status_id' => [
                $orderId ? 'sometimes' : 'required',
                'integer',
                'exists:order_statuses,id'
            ],
            'total' => [
                $orderId ? 'sometimes' : 'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'subtotal' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'tax_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'shipping_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'currency' => [
                'nullable',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'shipping_address' => [
                'nullable',
                'string',
                'min:10',
                'max:500'
            ],
            'billing_address' => [
                'nullable',
                'string',
                'min:10',
                'max:500'
            ],
            'shipping_method' => [
                'nullable',
                'string',
                'max:100'
            ],
            'products' => [
                'required',
                'array',
                'min:1',
                'max:50'
            ],
            'products.*.product_id' => [
                'required_with:products',
                'integer',
                'exists:products,id'
            ],
            'products.*.quantity' => [
                'required_with:products',
                'integer',
                'min:1',
                'max:999'
            ],
            'products.*.price' => [
                'required_with:products',
                'numeric',
                'min:0.01',
                'max:99999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer is required.',
            'customer_id.integer' => 'Customer ID must be an integer.',
            'customer_id.exists' => 'Selected customer does not exist.',
            
            'status_id.required' => 'Order status is required.',
            'status_id.integer' => 'Status ID must be an integer.',
            'status_id.exists' => 'Selected status does not exist.',
            
            'total.required' => 'Total amount is required.',
            'total.numeric' => 'Total amount must be a number.',
            'total.min' => 'Total amount must be at least 0.01.',
            'total.max' => 'Total amount may not be greater than 999,999.99.',
            'total.regex' => 'Total amount must be in valid format (e.g., 10.99).',
            
            'subtotal.numeric' => 'Subtotal must be a number.',
            'subtotal.min' => 'Subtotal must be at least 0.',
            'subtotal.max' => 'Subtotal may not be greater than 999,999.99.',
            'subtotal.regex' => 'Subtotal must be in valid format (e.g., 10.99).',
            
            'tax_amount.numeric' => 'Tax amount must be a number.',
            'tax_amount.min' => 'Tax amount must be at least 0.',
            'tax_amount.max' => 'Tax amount may not be greater than 99,999.99.',
            'tax_amount.regex' => 'Tax amount must be in valid format (e.g., 10.99).',
            
            'shipping_amount.numeric' => 'Shipping amount must be a number.',
            'shipping_amount.min' => 'Shipping amount must be at least 0.',
            'shipping_amount.max' => 'Shipping amount may not be greater than 99,999.99.',
            'shipping_amount.regex' => 'Shipping amount must be in valid format (e.g., 10.99).',
            
            'discount_amount.numeric' => 'Discount amount must be a number.',
            'discount_amount.min' => 'Discount amount must be at least 0.',
            'discount_amount.max' => 'Discount amount may not be greater than 99,999.99.',
            'discount_amount.regex' => 'Discount amount must be in valid format (e.g., 10.99).',
            
            'currency.size' => 'Currency must be exactly 3 characters.',
            'currency.regex' => 'Currency must be in uppercase format (e.g., USD).',
            
            'notes.max' => 'Notes may not be greater than 1000 characters.',
            
            'shipping_address.min' => 'Shipping address must be at least 10 characters.',
            'shipping_address.max' => 'Shipping address may not be greater than 500 characters.',
            
            'billing_address.min' => 'Billing address must be at least 10 characters.',
            'billing_address.max' => 'Billing address may not be greater than 500 characters.',
            
            'shipping_method.max' => 'Shipping method may not be greater than 100 characters.',
            
            
            'products.required' => 'At least one product is required.',
            'products.array' => 'Products must be an array.',
            'products.min' => 'At least one product is required.',
            'products.max' => 'You may not have more than 50 products.',
            
            'products.*.product_id.required_with' => 'Product ID is required for each product.',
            'products.*.product_id.integer' => 'Product ID must be an integer.',
            'products.*.product_id.exists' => 'Selected product does not exist.',
            
            'products.*.quantity.required_with' => 'Quantity is required for each product.',
            'products.*.quantity.integer' => 'Quantity must be an integer.',
            'products.*.quantity.min' => 'Quantity must be at least 1.',
            'products.*.quantity.max' => 'Quantity may not be greater than 999.',
            
            'products.*.price.required_with' => 'Price is required for each product.',
            'products.*.price.numeric' => 'Price must be a number.',
            'products.*.price.min' => 'Price must be at least 0.01.',
            'products.*.price.max' => 'Price may not be greater than 99,999.99.',
            'products.*.price.regex' => 'Price must be in valid format (e.g., 10.99).'
        ];
    }

    /**
     * Get the customer ID.
     */
    public function getCustomerId(): int
    {
        return $this->input('customer_id');
    }

    /**
     * Get the status ID.
     */
    public function getStatusId(): ?int
    {
        return $this->input('status_id');
    }

    /**
     * Get the total amount.
     */
    public function getTotal(): ?float
    {
        return $this->input('total');
    }

    /**
     * Get the subtotal.
     */
    public function getSubtotal(): ?float
    {
        return $this->input('subtotal');
    }

    /**
     * Get the tax amount.
     */
    public function getTaxAmount(): ?float
    {
        return $this->input('tax_amount');
    }

    /**
     * Get the shipping amount.
     */
    public function getShippingAmount(): ?float
    {
        return $this->input('shipping_amount');
    }

    /**
     * Get the discount amount.
     */
    public function getDiscountAmount(): ?float
    {
        return $this->input('discount_amount');
    }

    /**
     * Get the currency.
     */
    public function getCurrency(): ?string
    {
        return $this->input('currency');
    }

    /**
     * Get the notes.
     */
    public function getNotes(): ?string
    {
        return $this->input('notes');
    }

    /**
     * Get the shipping address.
     */
    public function getShippingAddress(): ?string
    {
        return $this->input('shipping_address');
    }

    /**
     * Get the billing address.
     */
    public function getBillingAddress(): ?string
    {
        return $this->input('billing_address');
    }

    /**
     * Get the shipping method.
     */
    public function getShippingMethod(): ?string
    {
        return $this->input('shipping_method');
    }

    /**
     * Get the tracking number.
     */

    /**
     * Get the order products.
     */
    public function getProducts(): ?array
    {
        return $this->input('products');
    }

    /**
     * Check if this is an update request.
     */
    public function isUpdate(): bool
    {
        return $this->route('order') !== null;
    }

    /**
     * Get the order ID for updates.
     */
    public function getOrderId(): ?int
    {
        return $this->route('order')?->getKey();
    }

    /**
     * Check if products are provided.
     */
    public function hasProducts(): bool
    {
        return !empty($this->getProducts());
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
