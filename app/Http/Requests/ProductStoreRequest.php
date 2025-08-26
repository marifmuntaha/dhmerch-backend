<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
            'sku' => 'required|unique:products,sku',
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required',
            'size' => 'required',
            'arm' => 'required',
            'file' => 'size:2048|mimes:jpg,jpeg,png',
            'status' => 'required',
        ];
    }

    public function attributes(): array
    {
        return [
            'sku' => 'SKU',
            'name' => 'Nama',
            'description' => 'Diskripsi',
            'price' => 'Harga',
            'size' => 'Ukuran',
            'arm' => 'Lengan',
            'file' => 'Gambar',
            'status' => 'Status',
        ];
    }
}
