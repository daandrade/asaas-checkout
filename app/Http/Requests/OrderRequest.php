<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'customer.name' => 'required|string|max:255',
            'customer.email' => 'required|email|max:255',
            'customer.document' => 'required|string|max:20|regex:/^\d{11}$/', // CPF com 11 dígitos
            'customer.phone' => 'required|string|max:20',
            'customer.address' => 'nullable|array',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:pix,boleto,credit_card',
        ];

        if ($this->input('payment_method') === 'credit_card') {
            $rules['credit_card.holder_name'] = 'required|string|max:100';
            $rules['credit_card.number'] = 'required|string|size:16';
            $rules['credit_card.expiry_month'] = 'required|string|size:2';
            $rules['credit_card.expiry_year'] = 'required|string|size:4';
            $rules['credit_card.cvv'] = 'required|string|size:3';
            $rules['credit_card.holder_document'] = 'required|string|size:11|same:customer.document'; // Mesmo documento
            $rules['installments'] = 'sometimes|integer|min:1|max:12';
        }

        return $rules;
    }
}