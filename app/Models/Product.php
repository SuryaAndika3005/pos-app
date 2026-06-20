<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'category',
        'image',
        'price',
        'cost_price',
        'unit',
        'unit_type',   // 'integer' (bulat) | 'decimal' (boleh pecahan)
        'stock',
        'min_stock',
        'is_active',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock'      => 'decimal:2',
        'min_stock'  => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    /**
     * Apakah produk dijual dalam satuan pecahan (kain, busa per meter, dll).
     */
    public function isFractional(): bool
    {
        return $this->unit_type === 'decimal';
    }

    /**
     * Apakah stok sudah di bawah ambang batas (untuk notifikasi / prediksi AI).
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
