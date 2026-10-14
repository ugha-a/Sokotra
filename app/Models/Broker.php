<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $logo_url
 * @property string $regulator_license_no
 * @property string|null $website_url
 * @property string $affiliate_url
 * @property list<array{name: string, min_deposit: int, spread_from: int|float, commission: int|float}> $account_types
 * @property string|null $description
 * @property string $rebate_per_lot_usd
 * @property int $sort_order
 * @property bool $is_active
 * @property-read int $account_type_count
 */
#[Fillable(['name', 'slug', 'logo_url', 'regulator_license_no', 'website_url', 'affiliate_url', 'account_types', 'description', 'rebate_per_lot_usd', 'sort_order', 'is_active'])]
class Broker extends Model
{
    /**
     * @return Attribute<int<0, max>, never>
     */
    protected function accountTypeCount(): Attribute
    {
        return Attribute::get(fn () => count($this->account_types));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_types' => 'array',
            'rebate_per_lot_usd' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
