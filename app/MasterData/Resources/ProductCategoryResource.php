<?php

namespace App\MasterData\Resources;

use App\MasterData\Field;
use App\MasterData\MasterResource;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ProductCategoryResource extends MasterResource
{
    public function key(): string
    {
        return 'product-categories';
    }

    public function label(): string
    {
        return 'Kategori Produk';
    }

    public function model(): string
    {
        return ProductCategory::class;
    }

    public function query(): Builder
    {
        return ProductCategory::query()->orderBy('name');
    }

    public function fields(): array
    {
        return [
            Field::text('name', 'Nama'),
            Field::boolean('is_active', 'Aktif', true),
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'name', 'label' => 'Nama', 'type' => 'text'],
            ['name' => 'slug', 'label' => 'Slug', 'type' => 'text'],
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
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', Rule::unique('product_categories')->ignore($record)],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
