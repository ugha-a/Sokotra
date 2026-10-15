<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Every time an admin opens a KYC document (docs/logic/compliance.md).
 *
 * @property int $id
 * @property int $kyc_verification_id
 * @property int|null $user_id
 * @property string $document
 * @property string|null $ip
 * @property Carbon $accessed_at
 */
#[Fillable(['kyc_verification_id', 'user_id', 'document', 'ip', 'accessed_at'])]
class KycAccessLog extends Model
{
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['accessed_at' => 'datetime'];
    }
}
