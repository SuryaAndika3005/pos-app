<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'address', 'notes',
        'total_debt', 'total_spent',
    ];

    protected $casts = [
        'total_debt'  => 'decimal:2',
        'total_spent' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function activeDebts(): HasMany
    {
        return $this->hasMany(Debt::class)->whereIn('status', ['open', 'partial']);
    }

    /** Sinkron ulang cache total_debt dari data aktual. */
    public function recalculateDebt(): void
    {
        $this->update([
            'total_debt' => $this->debts()
                ->whereIn('status', ['open', 'partial'])
                ->sum('remaining_amount'),
        ]);
    }

    /** Sinkron ulang cache total_spent dari data aktual. */
    public function recalculateSpent(): void
    {
        $this->update([
            'total_spent' => $this->transactions()
                ->where('status', 'paid')
                ->sum('total'),
        ]);
    }

    public function hasDebt(): bool
    {
        return $this->total_debt > 0;
    }
}
