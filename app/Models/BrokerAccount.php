<?php

namespace App\Models;

use App\Enums\BrokerAccountStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $broker_id
 * @property string $account_login
 * @property string|null $account_type
 * @property BrokerAccountStatus $status
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property-read User $user
 * @property-read Broker $broker
 */
#[Fillable(['user_id', 'broker_id', 'account_login', 'account_type', 'status', 'verified_by', 'verified_at', 'notes'])]
class BrokerAccount extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Broker, $this>
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['status' => BrokerAccountStatus::class, 'verified_at' => 'datetime'];
    }
}
