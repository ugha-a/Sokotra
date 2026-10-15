<?php

namespace App\Models;

use App\Enums\FraudFlagStatus;
use App\Enums\FraudFlagType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Suspicious pattern awaiting admin review (docs/logic/scoring-ranking.md → Anti-Fraud).
 *
 * @property int $id
 * @property FraudFlagType $type
 * @property FraudFlagStatus $status
 * @property list<int> $subject_user_ids
 * @property list<int>|null $signal_ids
 * @property array<string, mixed>|null $details
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 * @property string|null $notes
 * @property Carbon|null $created_at
 */
#[Fillable(['type', 'status', 'subject_user_ids', 'signal_ids', 'details', 'reviewed_by', 'reviewed_at', 'notes'])]
class FraudFlag extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => FraudFlagType::class,
            'status' => FraudFlagStatus::class,
            'subject_user_ids' => 'array',
            'signal_ids' => 'array',
            'details' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }
}
