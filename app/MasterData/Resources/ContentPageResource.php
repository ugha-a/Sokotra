<?php

namespace App\MasterData\Resources;

use App\MasterData\Field;
use App\MasterData\MasterResource;
use App\Models\ContentPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ContentPageResource extends MasterResource
{
    public function key(): string
    {
        return 'content-pages';
    }

    public function label(): string
    {
        return 'Halaman Konten';
    }

    public function description(): ?string
    {
        return 'Tampil di /pages/{slug}. Isi markdown. S&K dan kebijakan privasi wajib ditinjau konsultan hukum.';
    }

    public function model(): string
    {
        return ContentPage::class;
    }

    public function query(): Builder
    {
        return ContentPage::query()->orderBy('title');
    }

    public function activeAttribute(): string
    {
        return 'is_published';
    }

    public function fields(): array
    {
        return [
            Field::text('title', 'Judul'),
            Field::text('slug', 'Slug', 'Huruf kecil dan tanda hubung, contoh syarat-ketentuan.'),
            Field::textarea('body', 'Isi (markdown)'),
            Field::boolean('is_published', 'Dipublikasikan'),
        ];
    }

    public function columns(): array
    {
        return [
            ['name' => 'title', 'label' => 'Judul', 'type' => 'text'],
            ['name' => 'slug', 'label' => 'Slug', 'type' => 'text'],
        ];
    }

    public function rules(?Model $record): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('content_pages')->ignore($record)],
            'body' => ['required', 'string'],
            'is_published' => ['required', 'boolean'],
        ];
    }
}
