<?php

namespace App\Models;

use App\Enums\PayrollPeriodStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $month
 * @property PayrollPeriodStatus $status
 * @property int $total_amount
 * @property Carbon|null $generated_at
 * @property int|null $approved_by
 * @property Carbon|null $approved_at
 */
#[Fillable(['month', 'status', 'total_amount', 'generated_at', 'approved_by', 'approved_at'])]
class PayrollPeriod extends Model
{
    /**
     * @return HasMany<PayrollItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function label(): string
    {
        return Carbon::createFromFormat('Y-m', $this->month)?->translatedFormat('F Y') ?? $this->month;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PayrollPeriodStatus::class,
            'total_amount' => 'integer',
            'generated_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }
}
