<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id', 'transaction_id', 'created_by',
        'original_amount', 'paid_amount', 'remaining_amount',
        'status', 'note', 'due_date',
    ];

    protected $casts = [
        'original_amount'  => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'due_date'         => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'paid';
    }

    /**
     * Terima pembayaran sebagian atau penuh.
     * Mengembalikan DebtPayment yang baru dibuat.
     */
    public function receivePayment(float $amount, string $method = 'cash', ?string $note = null, ?int $receivedBy = null): DebtPayment
    {
        $amount = min($amount, (float) $this->remaining_amount); // tidak boleh lebih dari sisa

        $payment = $this->payments()->create([
            'customer_id' => $this->customer_id,
            'received_by' => $receivedBy,
            'amount'      => $amount,
            'method'      => $method,
            'note'        => $note,
        ]);

        $newPaid      = (float) $this->paid_amount + $amount;
        $newRemaining = (float) $this->original_amount - $newPaid;
        $newStatus    = $newRemaining <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'open');

        $this->update([
            'paid_amount'      => $newPaid,
            'remaining_amount' => max(0, $newRemaining),
            'status'           => $newStatus,
        ]);

        // Sinkron cache total_debt di customer
        $this->customer->recalculateDebt();

        return $payment;
    }
}
