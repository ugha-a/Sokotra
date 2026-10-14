<?php

namespace App\Models;

use App\Enums\InstrumentCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $symbol
 * @property string $name
 * @property InstrumentCategory $category
 * @property string $pip_size
 * @property int $digits
 * @property string $spread_pips
 * @property string $min_sl_pips
 * @property string|null $feed_symbol
 * @property bool $is_active
 * @property-read string $category_label
 */
#[Fillable(['symbol', 'name', 'category', 'pip_size', 'digits', 'spread_pips', 'min_sl_pips', 'feed_symbol', 'is_active'])]
class Instrument extends Model
{
    /**
     * @return Attribute<string, never>
     */
    protected function categoryLabel(): Attribute
    {
        return Attribute::get(fn () => $this->category->label());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => InstrumentCategory::class,
            'pip_size' => 'decimal:5',
            'digits' => 'integer',
            'spread_pips' => 'decimal:2',
            'min_sl_pips' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
