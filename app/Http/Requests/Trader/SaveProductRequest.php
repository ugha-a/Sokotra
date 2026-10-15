<?php

namespace App\Http\Requests\Trader;

use App\Enums\ProductPlatform;
use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveProductRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $creating = $this->route('product') === null;
        $maxKb = Settings::get('marketplace.max_file_mb') * 1024;

        return [
            'category_id' => ['required', 'integer', Rule::exists('product_categories', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'summary' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'platform' => ['required', Rule::enum(ProductPlatform::class)],
            'price' => ['required', 'integer', 'min:0'],
            'version' => ['required', 'string', 'max:20'],
            'tradingview_url' => ['nullable', 'required_if:platform,tradingview', 'url', 'max:255'],
            'file' => [
                $creating ? 'required_unless:platform,tradingview' : 'nullable',
                'nullable',
                'file',
                'extensions:ex4,ex5,mq4,mq5,zip',
                "max:{$maxKb}",
            ],
            'screenshots' => ['nullable', 'array', 'max:5'],
            'screenshots.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'kategori',
            'name' => 'nama produk',
            'summary' => 'ringkasan',
            'description' => 'deskripsi',
            'price' => 'harga',
            'version' => 'versi',
            'tradingview_url' => 'link TradingView',
            'file' => 'file produk',
            'screenshots' => 'screenshot',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.extensions' => 'File harus berformat .ex4, .ex5, .mq4, .mq5, atau .zip.',
            'file.required_unless' => 'File produk wajib untuk MetaTrader.',
            'tradingview_url.required_if' => 'Link TradingView wajib untuk produk TradingView.',
        ];
    }
}
