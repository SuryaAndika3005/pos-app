<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'payment_method',
        'paid_amount',
        'change_amount',
        'status',
        'note',
    ];

    protected $casts = [
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'tax'           => 'decimal:2',
        'total'         => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    /**
     * Header punya banyak baris detail.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Kasir yang memproses transaksi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
