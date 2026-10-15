<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * IB rebate per broker account per month (docs/logic/broker.md → Rebate IB).
 *
 * @property int $id
 * @property int $broker_id
 * @property int|null $broker_account_id
 * @property string $account_login
 * @property string $period
 * @property string $lots
 * @property string $rebate_usd
 * @property string $fx_rate
 * @property int $rebate_idr
 * @property-read Broker $broker
 * @property-read BrokerAccount|null $brokerAccount
 */
#[Fillable(['broker_id', 'broker_account_id', 'account_login', 'period', 'lots', 'rebate_usd', 'fx_rate', 'rebate_idr'])]
class BrokerRebate extends Model
{
    /**
     * @return BelongsTo<Broker, $this>
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    /**
     * @return BelongsTo<BrokerAccount, $this>
     */
    public function brokerAccount(): BelongsTo
    {
        return $this->belongsTo(BrokerAccount::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['lots' => 'decimal:2', 'rebate_usd' => 'decimal:2', 'fx_rate' => 'decimal:2', 'rebate_idr' => 'integer'];
    }
}
