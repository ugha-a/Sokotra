<?php

namespace App\Models;

use App\Enums\SettingGroup;
use App\Enums\SettingType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property SettingGroup $group
 * @property SettingType $type
 * @property string $label
 * @property string|null $description
 * @property array<string, string>|null $options
 * @property mixed $value
 * @property int $sort_order
 */
#[Fillable(['key', 'group', 'type', 'label', 'description', 'options', 'value', 'sort_order'])]
class PlatformSetting extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'group' => SettingGroup::class,
            'type' => SettingType::class,
            'options' => 'array',
            'value' => 'json',
            'sort_order' => 'integer',
        ];
    }
}
