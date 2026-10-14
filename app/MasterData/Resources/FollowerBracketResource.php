<?php

namespace App\MasterData\Resources;

use App\MasterData\Field;
use App\MasterData\MasterResource;
use App\Models\FollowerBracket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Validator;

class FollowerBracketResource extends MasterResource
{
    public function key(): string
    {
        return 'follower-brackets';
    }

    public function label(): string
    {
        return 'Bracket Followers';
    }

    public function description(): ?string
    {
        return 'Bonus gaji berdasarkan jumlah follower valid. Rentang bracket aktif tidak boleh bertabrakan.';
    }

    public function model(): string
    {
        return FollowerBracket::class;
    }

    public function query(): Builder
    {
        return FollowerBracket::query()->orderBy('min_followers');
    }

    public function fields(): array
    {
        return [
            Field::integer('min_followers', 'Followers minimum'),
            Field::integer('max_followers', 'Followers maksimum', 'Kosongkan untuk tanpa batas atas.'),
            Field::money('bonus_amount', 'Bonus / bulan'),
            Field::boolean('is_active', 'Aktif', true),
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'min_followers', 'label' => 'Dari', 'type' => 'integer'],
            ['name' => 'max_followers', 'label' => 'Sampai', 'type' => 'integer'],
            ['name' => 'bonus_amount', 'label' => 'Bonus', 'type' => 'money'],
        ];
    }

    public function rules(?Model $record): array
    {
        return [
            'min_followers' => ['required', 'integer', 'min:0'],
            'max_followers' => ['nullable', 'integer', 'gte:min_followers'],
            'bonus_amount' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(Validator $validator, array $input, ?Model $record): void
    {
        if ($validator->errors()->isNotEmpty() || ! filter_var($input['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $min = (int) $input['min_followers'];
        $max = isset($input['max_followers']) ? (int) $input['max_followers'] : PHP_INT_MAX;

        $overlap = FollowerBracket::query()
            ->where('is_active', true)
            ->when($record, fn (Builder $query) => $query->whereKeyNot($record->getKey()))
            ->get()
            ->first(fn (FollowerBracket $bracket) => $min <= ($bracket->max_followers ?? PHP_INT_MAX)
                && $bracket->min_followers <= $max);

        if ($overlap) {
            $range = $overlap->min_followers.'–'.($overlap->max_followers ?? '∞');
            $validator->errors()->add('min_followers', "Rentang bertabrakan dengan bracket aktif {$range}.");
        }
    }
}
