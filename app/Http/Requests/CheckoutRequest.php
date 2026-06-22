<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items'           => ['required', 'array', 'min:1'],
            'items.*.id'      => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'     => ['required', 'numeric', 'gt:0'],

            'customer_name'   => ['nullable', 'string', 'max:100'],
            'customer_id'     => ['nullable', 'integer', 'exists:customers,id'],
            'payment_method'  => ['nullable', 'in:cash,qris,transfer,debit'],
            'paid_amount'     => ['nullable', 'numeric', 'min:0'],
            'due_date'        => ['nullable', 'date', 'after:today'],
            'note'            => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'    => 'Keranjang tidak boleh kosong.',
            'items.*.id.exists' => 'Produk tidak ditemukan.',
            'items.*.qty.gt'    => 'Kuantitas harus lebih dari 0.',
            'due_date.after'    => 'Tenggat utang harus setelah hari ini.',
        ];
    }
}
