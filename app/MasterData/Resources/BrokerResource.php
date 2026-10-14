<?php

namespace App\MasterData\Resources;

use App\MasterData\Field;
use App\MasterData\MasterResource;
use App\Models\Broker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BrokerResource extends MasterResource
{
    public function key(): string
    {
        return 'brokers';
    }

    public function label(): string
    {
        return 'Broker';
    }

    public function description(): ?string
    {
        return 'Hanya broker berizin Bappebti. Cek nomor izin di situs Bappebti sebelum mengaktifkan.';
    }

    public function model(): string
    {
        return Broker::class;
    }

    public function query(): Builder
    {
        return Broker::query()->orderBy('sort_order')->orderBy('name');
    }

    public function fields(): array
    {
        return [
            Field::text('name', 'Nama'),
            Field::text('regulator_license_no', 'Nomor izin Bappebti'),
            Field::url('website_url', 'Website'),
            Field::text('affiliate_url', 'Link afiliasi / IB', 'Gunakan {ref} sebagai tempat kode referral, contoh https://broker.co.id/daftar?ib={ref}.'),
            Field::url('logo_url', 'URL logo'),
            Field::textarea('description', 'Deskripsi'),
            Field::repeater('account_types', 'Tipe akun', [
                Field::text('name', 'Nama'),
                Field::money('min_deposit', 'Deposit minimum'),
                Field::decimal('spread_from', 'Spread mulai (pips)'),
                Field::decimal('commission', 'Komisi / lot (USD)'),
            ]),
            Field::decimal('rebate_per_lot_usd', 'Rebate IB / lot (USD)', 'Informasi internal, tidak tampil ke member.'),
            Field::integer('sort_order', 'Urutan tampil'),
            Field::boolean('is_active', 'Aktif', true),
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'name', 'label' => 'Nama', 'type' => 'text'],
            ['name' => 'regulator_license_no', 'label' => 'Izin Bappebti', 'type' => 'text'],
            ['name' => 'account_type_count', 'label' => 'Tipe akun', 'type' => 'integer'],
            ['name' => 'sort_order', 'label' => 'Urutan', 'type' => 'integer'],
        ];
    }

    public function prepareForValidation(array $input, ?Model $record): array
    {
        $input['slug'] = $record?->getAttribute('slug') ?? str((string) ($input['name'] ?? ''))->slug()->toString();
        $input['account_types'] ??= [];

        return $input;
    }

    public function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', Rule::unique('brokers')->ignore($record)],
            'regulator_license_no' => ['required', 'string', 'max:100'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'affiliate_url' => ['required', 'string', 'max:500'],
            'logo_url' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'account_types' => ['present', 'array'],
            'account_types.*.name' => ['required', 'string', 'max:50'],
            'account_types.*.min_deposit' => ['required', 'integer', 'min:0'],
            'account_types.*.spread_from' => ['required', 'numeric', 'min:0'],
            'account_types.*.commission' => ['required', 'numeric', 'min:0'],
            'rebate_per_lot_usd' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(Validator $validator, array $input, ?Model $record): void
    {
        $url = str_replace('{ref}', 'REF', (string) ($input['affiliate_url'] ?? ''));

        if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL) === false) {
            $validator->errors()->add('affiliate_url', 'Link afiliasi harus berupa URL yang valid.');
        }
    }

    public function beforeSave(array $validated): array
    {
        // Form inputs arrive as strings; store numbers as numbers inside the JSON column.
        $validated['account_types'] = array_map(
            fn (array $type) => array_map(fn ($value) => is_numeric($value) ? $value + 0 : $value, $type),
            $validated['account_types'],
        );

        return $validated;
    }

    public function appends(): array
    {
        return ['account_type_count'];
    }
}
