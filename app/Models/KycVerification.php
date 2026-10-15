<?php

namespace App\Models;

use App\Enums\KycStatus;
use Database\Factories\KycVerificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One KYC record per user (docs/logic/compliance.md). NIK is encrypted at rest;
 * nik_hash enforces one NIK per account.
 *
 * @property int $id
 * @property int $user_id
 * @property string $full_name
 * @property string $nik
 * @property string $nik_hash
 * @property Carbon $birth_date
 * @property string $id_card_path
 * @property string $selfie_path
 * @property KycStatus $status
 * @property Carbon|null $submitted_at
 * @property Carbon|null $reviewed_at
 * @property int|null $reviewed_by
 * @property string|null $rejection_reason
 * @property-read User $user
 * @property-read User|null $reviewer
 */
#[Fillable(['user_id', 'full_name', 'nik', 'nik_hash', 'birth_date', 'id_card_path', 'selfie_path', 'status', 'submitted_at', 'reviewed_at', 'reviewed_by', 'rejection_reason'])]
#[Hidden(['nik', 'nik_hash', 'id_card_path', 'selfie_path'])]
class KycVerification extends Model
{
    /** @use HasFactory<KycVerificationFactory> */
    use HasFactory;

    /** Documents an admin may open, keyed by route segment. */
    public const DOCUMENTS = ['id_card' => 'id_card_path', 'selfie' => 'selfie_path'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nik' => 'encrypted',
            'birth_date' => 'date',
            'status' => KycStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }
}
