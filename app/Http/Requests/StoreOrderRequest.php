<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Order; // Import the Order model

class StoreOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Set this to false if you implement specific authorization logic
    }

    public function rules()
    {
        return [
            'payment_status' => 'required|in:' . implode(',', [Order::PAYMENT_HANDCASH, Order::PAYMENT_CHEQUE, Order::PAYMENT_DUE]),
            'pay' => 'required|numeric|min:0',
            'customer_id' => 'required|exists:customers,id',
        ];
    }

    public function messages()
    {
        return [
            'payment_status.required' => 'Please select a valid payment method.',
            'pay.required' => 'The payment amount is required.',
            'customer_id.exists' => 'Selected customer does not exist.'
        ];
    }
}
