<?php

namespace App\MasterData\Resources;

use App\MasterData\Field;
use App\MasterData\MasterResource;
use App\Models\Rank;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class RankResource extends MasterResource
{
    public function key(): string
    {
        return 'ranks';
    }

    public function label(): string
    {
        return 'Rank';
    }

    public function description(): ?string
    {
        return 'Threshold rank dihitung dari statistik 90 hari terakhir. Rank tertinggi yang semua syaratnya terpenuhi yang dipakai.';
    }

    public function model(): string
    {
        return Rank::class;
    }

    public function query(): Builder
    {
        return Rank::query()->orderBy('level');
    }

    public function fields(): array
    {
        return [
            Field::text('name', 'Nama'),
            Field::integer('level', 'Level', '1 = terendah. Harus unik.'),
            Field::integer('min_closed_signals', 'Minimal sinyal closed'),
            Field::decimal('min_win_rate', 'Minimal win rate (%)'),
            Field::decimal('min_total_r', 'Minimal total R'),
            Field::integer('min_valid_followers', 'Minimal follower valid'),
            Field::decimal('max_drawdown_r', 'Maksimal drawdown (R)', 'Kosongkan jika tidak dibatasi.'),
            Field::boolean('can_sell_premium', 'Boleh membuka channel premium'),
            Field::money('subscription_min_price', 'Harga subscription minimum / bulan', 'Wajib jika boleh membuka premium.'),
            Field::money('subscription_max_price', 'Harga subscription maksimum / bulan'),
            Field::boolean('can_sell_products', 'Boleh jual di marketplace'),
            Field::select('color', 'Warna badge', array_map(
                fn (string $token) => ['value' => $token, 'label' => $token],
                Rank::COLORS,
            )),
            Field::boolean('is_active', 'Aktif', true),
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'level', 'label' => 'Level', 'type' => 'integer'],
            ['name' => 'name', 'label' => 'Nama', 'type' => 'badge'],
            ['name' => 'min_closed_signals', 'label' => 'Sinyal', 'type' => 'integer'],
            ['name' => 'min_win_rate', 'label' => 'Win rate', 'type' => 'percent'],
            ['name' => 'min_total_r', 'label' => 'Total R', 'type' => 'decimal'],
            ['name' => 'min_valid_followers', 'label' => 'Followers', 'type' => 'integer'],
            ['name' => 'can_sell_premium', 'label' => 'Premium', 'type' => 'boolean'],
            ['name' => 'can_sell_products', 'label' => 'Produk', 'type' => 'boolean'],
        ];
    }

    public function prepareForValidation(array $input, ?Model $record): array
    {
        $input['slug'] = $record?->getAttribute('slug') ?? str((string) ($input['name'] ?? ''))->slug()->toString();

        return $input;
    }

    public function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'slug' => ['required', 'string', Rule::unique('ranks')->ignore($record)],
            'level' => ['required', 'integer', 'between:1,50', Rule::unique('ranks')->ignore($record)],
            'min_closed_signals' => ['required', 'integer', 'min:0'],
            'min_win_rate' => ['required', 'numeric', 'between:0,100'],
            'min_total_r' => ['required', 'numeric'],
            'min_valid_followers' => ['required', 'integer', 'min:0'],
            'max_drawdown_r' => ['nullable', 'numeric', 'min:0'],
            'can_sell_premium' => ['required', 'boolean'],
            'subscription_min_price' => ['nullable', 'required_if_accepted:can_sell_premium', 'integer', 'min:0'],
            'subscription_max_price' => ['nullable', 'required_if_accepted:can_sell_premium', 'integer', 'gte:subscription_min_price'],
            'can_sell_products' => ['required', 'boolean'],
            'color' => ['required', Rule::in(Rank::COLORS)],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
