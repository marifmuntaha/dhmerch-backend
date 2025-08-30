<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PaymentStoreRequest extends FormRequest
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
            'name' => 'required',
            'phone' => 'required',
            'amount' => 'required',
            'productId' => 'required',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Lengkap',
            'phone' => 'Nomor WA',
            'amount' => 'Alamat',
            'productId' => 'SKU Produk',
        ];
    }
}
