<?php

namespace App\MasterData;

/**
 * Builders for the form schema rendered by components/domain/master-form.tsx.
 * Field types must stay in sync with MasterFieldType in resources/js/types/master.ts.
 */
class Field
{
    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public static function make(string $type, string $name, string $label, array $extra = []): array
    {
        return array_filter(
            ['name' => $name, 'label' => $label, 'type' => $type, ...$extra],
            fn ($value) => $value !== null,
        );
    }

    /** @return array<string, mixed> */
    public static function text(string $name, string $label, ?string $help = null): array
    {
        return self::make('text', $name, $label, ['help' => $help]);
    }

    /** @return array<string, mixed> */
    public static function textarea(string $name, string $label, ?string $help = null): array
    {
        return self::make('textarea', $name, $label, ['help' => $help]);
    }

    /** @return array<string, mixed> */
    public static function url(string $name, string $label, ?string $help = null): array
    {
        return self::make('url', $name, $label, ['help' => $help]);
    }

    /** @return array<string, mixed> */
    public static function integer(string $name, string $label, ?string $help = null): array
    {
        return self::make('integer', $name, $label, ['help' => $help]);
    }

    /** @return array<string, mixed> */
    public static function decimal(string $name, string $label, ?string $help = null, string $step = '0.01'): array
    {
        return self::make('decimal', $name, $label, ['help' => $help, 'step' => $step]);
    }

    /** @return array<string, mixed> */
    public static function money(string $name, string $label, ?string $help = null): array
    {
        return self::make('money', $name, $label, ['help' => $help]);
    }

    /** @return array<string, mixed> */
    public static function boolean(string $name, string $label, bool $default = false, ?string $help = null): array
    {
        return self::make('boolean', $name, $label, ['help' => $help, 'default' => $default]);
    }

    /**
     * @param  list<array{value: string|int, label: string}>  $options
     * @return array<string, mixed>
     */
    public static function select(string $name, string $label, array $options, ?string $help = null): array
    {
        return self::make('select', $name, $label, ['help' => $help, 'options' => $options]);
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    public static function repeater(string $name, string $label, array $fields, ?string $help = null): array
    {
        return self::make('repeater', $name, $label, ['help' => $help, 'fields' => $fields]);
    }
}
