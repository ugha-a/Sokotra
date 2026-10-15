<?php

namespace App\Support\Signal;

use App\Enums\SignalStatus;
use App\Http\Resources\SignalResource;
use App\Models\Instrument;
use App\Models\Signal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Shared filtering for the member feed and the trader's own signal list.
 */
class SignalQuery
{
    /**
     * @return array{instrument: int|null, status: string}
     */
    public static function filters(Request $request): array
    {
        $validated = $request->validate([
            'instrument' => ['nullable', 'integer', 'exists:instruments,id'],
            'status' => ['nullable', 'in:all,open,closed'],
        ]);

        return [
            'instrument' => isset($validated['instrument']) ? (int) $validated['instrument'] : null,
            'status' => $validated['status'] ?? 'all',
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function statusOptions(): array
    {
        return [
            ['value' => 'all', 'label' => 'Semua'],
            ['value' => 'open', 'label' => 'Terbuka'],
            ['value' => 'closed', 'label' => 'Selesai'],
        ];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public static function instrumentOptions(): array
    {
        return array_values(array_map(
            fn (Instrument $instrument) => ['value' => $instrument->id, 'label' => $instrument->symbol],
            Instrument::query()->orderBy('symbol')->get()->all(),
        ));
    }

    /**
     * @param  Builder<Signal>  $query
     * @param  array{instrument: int|null, status: string}  $filters
     * @return LengthAwarePaginator<int, array<array-key, mixed>>
     */
    public static function paginate(Builder $query, array $filters, Request $request): LengthAwarePaginator
    {
        return $query
            ->with(['instrument', 'trader.traderProfile.rank'])
            ->when($filters['instrument'], fn (Builder $q, int $id) => $q->where('instrument_id', $id))
            ->when($filters['status'] === 'open', fn (Builder $q) => $q->whereIn('status', SignalStatus::open()))
            ->when($filters['status'] === 'closed', fn (Builder $q) => $q->whereNotIn('status', SignalStatus::open()))
            ->latest('published_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Signal $signal) => (new SignalResource($signal))->resolve($request));
    }
}
