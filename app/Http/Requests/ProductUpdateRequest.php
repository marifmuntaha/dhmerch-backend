<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
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
            'color' => 'required',
            'size' => 'required',
            'arm' => 'required',
            'image' => 'required',
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
            'color' => 'Warna',
            'size' => 'Ukuran',
            'arm' => 'Lengan',
            'image' => 'Gambar',
            'status' => 'Status',
        ];
    }
}
