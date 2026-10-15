<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $broker_id
 * @property string $ref_code
 * @property Carbon $clicked_at
 */
#[Fillable(['user_id', 'broker_id', 'ref_code', 'clicked_at'])]
class BrokerReferral extends Model
{
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['clicked_at' => 'datetime'];
    }
}
