<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sesuaikan dengan policy/role bila sudah ada autentikasi.
        return true;
    }

    public function rules(): array
    {
        return [
            'items'           => ['required', 'array', 'min:1'],
            'items.*.id'      => ['required', 'integer', 'exists:products,id'],
            // 'numeric' + 'gt:0' MENGIZINKAN desimal (2.5, 1.5) — tidak dibulatkan.
            'items.*.qty'     => ['required', 'numeric', 'gt:0'],

            'payment_method'  => ['nullable', 'in:cash,qris,transfer,debit'],
            'paid_amount'     => ['nullable', 'numeric', 'min:0'],
            'note'            => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'    => 'Keranjang tidak boleh kosong.',
            'items.*.id.exists' => 'Produk tidak ditemukan.',
            'items.*.qty.gt'    => 'Kuantitas harus lebih dari 0.',
        ];
    }
}
