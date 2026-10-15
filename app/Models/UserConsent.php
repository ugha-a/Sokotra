<?php

namespace App\Models;

use App\Enums\ConsentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Append-only record of accepted terms (docs/logic/compliance.md).
 *
 * @property int $id
 * @property int $user_id
 * @property ConsentType $type
 * @property string $version
 * @property Carbon $accepted_at
 * @property string|null $ip
 */
#[Fillable(['user_id', 'type', 'version', 'accepted_at', 'ip'])]
class UserConsent extends Model
{
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ConsentType::class,
            'accepted_at' => 'datetime',
        ];
    }
}
