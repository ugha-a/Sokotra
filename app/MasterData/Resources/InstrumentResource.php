<?php

namespace App\MasterData\Resources;

use App\Enums\InstrumentCategory;
use App\MasterData\Field;
use App\MasterData\MasterResource;
use App\Models\Instrument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class InstrumentResource extends MasterResource
{
    public function key(): string
    {
        return 'instruments';
    }

    public function label(): string
    {
        return 'Instrumen';
    }

    public function description(): ?string
    {
        return 'Pair yang boleh dipakai trader untuk sinyal. Pip size dan spread disalin ke sinyal saat publish.';
    }

    public function model(): string
    {
        return Instrument::class;
    }

    public function query(): Builder
    {
        return Instrument::query()->orderBy('category')->orderBy('symbol');
    }

    public function fields(): array
    {
        return [
            Field::text('symbol', 'Simbol', 'Huruf besar tanpa spasi, contoh EURUSD.'),
            Field::text('name', 'Nama'),
            Field::select('category', 'Kategori', InstrumentCategory::options()),
            Field::decimal('pip_size', 'Pip size', 'EURUSD 0.0001, USDJPY 0.01, XAUUSD 0.1.', '0.00001'),
            Field::integer('digits', 'Jumlah desimal harga'),
            Field::decimal('spread_pips', 'Spread default (pips)'),
            Field::decimal('min_sl_pips', 'Jarak SL minimum (pips)'),
            Field::text('feed_symbol', 'Simbol di price feed', 'Kode simbol pada provider harga, contoh EUR/USD.'),
            Field::boolean('is_active', 'Aktif', true),
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'symbol', 'label' => 'Simbol', 'type' => 'text'],
            ['name' => 'name', 'label' => 'Nama', 'type' => 'text'],
            ['name' => 'category_label', 'label' => 'Kategori', 'type' => 'text'],
            ['name' => 'pip_size', 'label' => 'Pip size', 'type' => 'text'],
            ['name' => 'spread_pips', 'label' => 'Spread', 'type' => 'decimal'],
            ['name' => 'min_sl_pips', 'label' => 'SL min', 'type' => 'decimal'],
        ];
    }

    public function prepareForValidation(array $input, ?Model $record): array
    {
        if (is_string($input['symbol'] ?? null)) {
            $input['symbol'] = strtoupper(trim($input['symbol']));
        }

        return $input;
    }

    public function rules(?Model $record): array
    {
        return [
            'symbol' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9]+$/', Rule::unique('instruments')->ignore($record)],
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', Rule::enum(InstrumentCategory::class)],
            'pip_size' => ['required', 'numeric', 'gt:0'],
            'digits' => ['required', 'integer', 'between:0,6'],
            'spread_pips' => ['required', 'numeric', 'min:0'],
            'min_sl_pips' => ['required', 'numeric', 'min:0'],
            'feed_symbol' => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function appends(): array
    {
        return ['category_label'];
    }
}
