<?php

namespace App\MasterData\Resources;

use App\MasterData\Field;
use App\MasterData\MasterResource;
use App\Models\PayrollRule;
use App\Models\Rank;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PayrollRuleResource extends MasterResource
{
    public function key(): string
    {
        return 'payroll-rules';
    }

    public function label(): string
    {
        return 'Aturan Gaji';
    }

    public function description(): ?string
    {
        return 'Satu aturan per rank. Rumus: gaji pokok + (analisa benar × tarif) + bonus followers + bonus streak.';
    }

    public function model(): string
    {
        return PayrollRule::class;
    }

    public function query(): Builder
    {
        return PayrollRule::query()
            ->with('rank')
            ->orderBy(Rank::select('level')->whereColumn('ranks.id', 'payroll_rules.rank_id'));
    }

    public function fields(): array
    {
        return [
            Field::select('rank_id', 'Rank', array_values(array_map(
                fn (Rank $rank) => ['value' => $rank->id, 'label' => $rank->name],
                Rank::query()->orderBy('level')->get()->all(),
            ))),
            Field::money('base_salary', 'Gaji pokok / bulan'),
            Field::money('per_win_amount', 'Tarif per analisa benar'),
            Field::integer('max_weekly_wins_counted', 'Maksimal analisa benar dibayar / minggu'),
            Field::integer('required_streak', 'Syarat streak menang berturut-turut'),
            Field::money('streak_bonus_amount', 'Bonus streak'),
            Field::integer('min_closed_signals_month', 'Minimal sinyal closed / bulan'),
            Field::decimal('max_drawdown_r_month', 'Maksimal drawdown / bulan (R)', 'Kosongkan jika tidak dibatasi.'),
            Field::boolean('is_active', 'Aktif', true),
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'rank_name', 'label' => 'Rank', 'type' => 'text'],
            ['name' => 'base_salary', 'label' => 'Gaji pokok', 'type' => 'money'],
            ['name' => 'per_win_amount', 'label' => 'Per analisa benar', 'type' => 'money'],
            ['name' => 'required_streak', 'label' => 'Syarat streak', 'type' => 'integer'],
            ['name' => 'streak_bonus_amount', 'label' => 'Bonus streak', 'type' => 'money'],
        ];
    }

    public function rules(?Model $record): array
    {
        return [
            'rank_id' => ['required', 'integer', 'exists:ranks,id', Rule::unique('payroll_rules')->ignore($record)],
            'base_salary' => ['required', 'integer', 'min:0'],
            'per_win_amount' => ['required', 'integer', 'min:0'],
            'max_weekly_wins_counted' => ['required', 'integer', 'min:1'],
            'required_streak' => ['required', 'integer', 'min:1'],
            'streak_bonus_amount' => ['required', 'integer', 'min:0'],
            'min_closed_signals_month' => ['required', 'integer', 'min:0'],
            'max_drawdown_r_month' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function appends(): array
    {
        return ['rank_name'];
    }
}
