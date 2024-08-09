<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreOrderRequest extends FormRequest
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
            'nama' => 'required',
            'whatsapp' => 'required',
            'produk' => 'required',
            'ukuran' => 'required',
            'warna' => 'required',
            'lengan' => 'required',
            'pembayaran' => 'required|image',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama' => 'Nama Pemesan',
            'whatsapp' => 'Nomor Whatsapp',
            'produk' => 'Produk',
            'ukuran' => 'Ukuran',
            'warna' => 'Warna',
            'lengan' => 'Lengan',
            'pembayaran' => 'Pembayaran',
        ];
    }

    public function prepareForValidation()
    {
        $number = Str::upper(Str::random(10));
        $extention = $this->file('pembayaran')->extension();
        $imagename = $number . '.' . $extention;
        Storage::putFileAs('public/images/pembayaran', $this->file('pembayaran'), $imagename);
        return $this->merge(['nomor' => $number, 'pembayaran' => $imagename]);
    }
}
