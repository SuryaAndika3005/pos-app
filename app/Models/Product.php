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
        // Kolom prediksi: diisi proses eksternal (model Python), bukan dari form ini.
        'daily_avg_usage',
        'predicted_stockout_at',
        'prediction_updated_at',
    ];

    protected $casts = [
        'price'                  => 'decimal:2',
        'cost_price'             => 'decimal:2',
        'stock'                  => 'decimal:2',
        'min_stock'              => 'decimal:2',
        'is_active'               => 'boolean',
        'daily_avg_usage'         => 'decimal:4',
        'predicted_stockout_at'   => 'date',
        'prediction_updated_at'   => 'datetime',
    ];

    /**
     * Apakah produk dijual dalam satuan pecahan (kain, busa per meter, dll).
     */
    public function isFractional(): bool
    {
        return $this->unit_type === 'decimal';
    }

    /**
     * Apakah stok sudah di bawah ambang batas (threshold statis dari kolom min_stock).
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    /**
     * Apakah produk ini sudah punya hasil prediksi dari model eksternal.
     */
    public function hasPrediction(): bool
    {
        return ! is_null($this->predicted_stockout_at);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
