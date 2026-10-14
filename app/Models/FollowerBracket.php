<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $min_followers
 * @property int|null $max_followers
 * @property int $bonus_amount
 * @property bool $is_active
 */
#[Fillable(['min_followers', 'max_followers', 'bonus_amount', 'is_active'])]
class FollowerBracket extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_followers' => 'integer',
            'max_followers' => 'integer',
            'bonus_amount' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
