<?php

namespace App\Enums;

enum SettingType: string
{
    case Integer = 'integer';
    case Decimal = 'decimal';
    case Money = 'money';
    case Percent = 'percent';
    case Boolean = 'boolean';
    case Text = 'text';
    case Choice = 'choice';
    case IntegerList = 'integer_list';
    case StringList = 'string_list';

    /**
     * Validation rules for a value of this type.
     *
     * @param  list<int|string>  $options  Allowed values for choice settings.
     * @return array<string, list<mixed>> Rules keyed by suffix ('' = the value itself, '.*' = list items).
     */
    public function rules(array $options = []): array
    {
        return match ($this) {
            self::Integer => ['' => ['required', 'integer', 'min:0']],
            self::Decimal => ['' => ['required', 'numeric', 'min:0']],
            self::Money => ['' => ['required', 'integer', 'min:0']],
            self::Percent => ['' => ['required', 'numeric', 'between:0,100']],
            self::Boolean => ['' => ['required', 'boolean']],
            self::Text => ['' => ['required', 'string', 'max:5000']],
            self::Choice => ['' => ['required', 'string', 'in:'.implode(',', $options)]],
            self::IntegerList => ['' => ['required', 'array', 'min:1'], '.*' => ['integer', 'min:1', 'distinct']],
            self::StringList => ['' => ['required', 'array', 'min:1'], '.*' => ['string', 'max:255', 'distinct']],
        };
    }

    /**
     * Cast a stored or submitted value to its PHP type.
     */
    public function cast(mixed $value): mixed
    {
        return match ($this) {
            self::Integer, self::Money => (int) $value,
            self::Decimal, self::Percent => (float) $value,
            self::Boolean => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::Text, self::Choice => (string) $value,
            self::IntegerList => array_values(array_map('intval', (array) $value)),
            self::StringList => array_values(array_map('strval', (array) $value)),
        };
    }
}
