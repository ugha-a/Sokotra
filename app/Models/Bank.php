<?php

namespace App\Models;

use App\Enums\BankType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property BankType $type
 * @property bool $is_active
 * @property-read string $type_label
 */
#[Fillable(['code', 'name', 'type', 'is_active'])]
class Bank extends Model
{
    /**
     * @return Attribute<string, never>
     */
    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->type->label());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => BankType::class,
            'is_active' => 'boolean',
        ];
    }
}
