<?php

namespace App\MasterData;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Validator;

/**
 * Describes one master table for the generic admin CRUD
 * (docs/logic/master-data.md, docs/ui-ux/components.md → MasterForm).
 */
abstract class MasterResource
{
    /** URL segment, e.g. "instruments". */
    abstract public function key(): string;

    abstract public function label(): string;

    /** @return class-string<Model> */
    abstract public function model(): string;

    /**
     * Form schema built with App\MasterData\Field.
     *
     * @return list<array<string, mixed>>
     */
    abstract public function fields(): array;

    /**
     * Table columns: name, label and a display type (text, integer, decimal, money, percent, boolean, badge).
     *
     * @return list<array{name: string, label: string, type: string}>
     */
    abstract public function columns(): array;

    /**
     * @return array<string, mixed>
     */
    abstract public function rules(?Model $record): array;

    public function description(): ?string
    {
        return null;
    }

    /** Boolean column flipped by the toggle action (records are never deleted). */
    public function activeAttribute(): string
    {
        return 'is_active';
    }

    /**
     * @return Builder<covariant Model>
     */
    public function query(): Builder
    {
        return $this->model()::query()->orderBy('id');
    }

    /**
     * Normalise raw input before validation (upper-casing codes, generating slugs, ...).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function prepareForValidation(array $input, ?Model $record): array
    {
        return $input;
    }

    /**
     * Cross-field checks that plain rules cannot express.
     *
     * @param  array<string, mixed>  $input
     */
    public function after(Validator $validator, array $input, ?Model $record): void {}

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function beforeSave(array $validated): array
    {
        return $validated;
    }

    /**
     * Computed model attributes (accessors) to include in each table row.
     *
     * @return list<string>
     */
    public function appends(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toRecord(Model $record): array
    {
        return $record->setAppends($this->appends())->attributesToArray();
    }

    /**
     * @return array<string, string>
     */
    public function attributeLabels(): array
    {
        $labels = [];

        foreach ($this->fields() as $field) {
            $labels[$field['name']] = $field['label'];

            foreach ($field['fields'] ?? [] as $child) {
                $labels["{$field['name']}.*.{$child['name']}"] = $child['label'];
            }
        }

        return $labels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key(),
            'label' => $this->label(),
            'description' => $this->description(),
            'fields' => $this->fields(),
            'columns' => $this->columns(),
            'activeAttribute' => $this->activeAttribute(),
        ];
    }
}
