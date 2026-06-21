<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pembatasan akses sudah ditangani middleware 'admin' di rute.
    }

    public function rules(): array
    {
        // Saat update, abaikan unique check untuk SKU milik produk itu sendiri.
        $productId = $this->route('product')?->id;

        return [
            'sku'         => ['nullable', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'category'    => ['nullable', 'string', 'max:50'],
            'price'       => ['required', 'numeric', 'min:0'],
            'cost_price'  => ['nullable', 'numeric', 'min:0'],
            'unit'        => ['required', 'string', 'max:20'],
            'unit_type'   => ['required', Rule::in(['integer', 'decimal'])],
            'stock'       => ['required', 'numeric', 'min:0'],
            'min_stock'   => ['required', 'numeric', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
            'image'       => ['nullable', 'image', 'max:2048'], // maks 2MB
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama produk wajib diisi.',
            'price.required' => 'Harga jual wajib diisi.',
            'sku.unique'     => 'SKU/kode barang ini sudah dipakai produk lain.',
        ];
    }
}
