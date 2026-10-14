<?php

namespace App\MasterData\Resources;

use App\Enums\BankType;
use App\MasterData\Field;
use App\MasterData\MasterResource;
use App\Models\Bank;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class BankResource extends MasterResource
{
    public function key(): string
    {
        return 'banks';
    }

    public function label(): string
    {
        return 'Bank & E-Wallet';
    }

    public function description(): ?string
    {
        return 'Tujuan penarikan saldo trader.';
    }

    public function model(): string
    {
        return Bank::class;
    }

    public function query(): Builder
    {
        return Bank::query()->orderBy('type')->orderBy('name');
    }

    public function fields(): array
    {
        return [
            Field::text('code', 'Kode', 'Huruf besar, contoh BCA.'),
            Field::text('name', 'Nama'),
            Field::select('type', 'Jenis', BankType::options()),
            Field::boolean('is_active', 'Aktif', true),
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
            ['name' => 'name', 'label' => 'Nama', 'type' => 'text'],
            ['name' => 'type_label', 'label' => 'Jenis', 'type' => 'text'],
        ];
    }

    public function prepareForValidation(array $input, ?Model $record): array
    {
        if (is_string($input['code'] ?? null)) {
            $input['code'] = strtoupper(trim($input['code']));
        }

        return $input;
    }

    public function rules(?Model $record): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('banks')->ignore($record)],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::enum(BankType::class)],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function appends(): array
    {
        return ['type_label'];
    }
}
