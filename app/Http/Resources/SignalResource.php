<?php

namespace App\Http\Resources;

use App\Models\Signal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

/**
 * Signal as shown to a viewer. Locked signals expose only instrument, direction and status.
 *
 * @mixin Signal
 */
class SignalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locked = Gate::forUser($request->user())->denies('viewDetails', $this->resource);
        $profile = $this->trader->traderProfile;
        $detail = fn ($value) => $locked ? null : $value;

        return [
            'id' => $this->id,
            'instrument' => ['symbol' => $this->instrument->symbol, 'digits' => $this->digits_snapshot],
            'trader' => [
                'name' => $profile->display_name ?? $this->trader->name,
                'slug' => $profile?->slug,
                'rank' => $profile?->rank ? ['name' => $profile->rank->name, 'color' => $profile->rank->color] : null,
            ],
            'direction' => $this->direction->value,
            'order_type' => $this->order_type->value,
            'order_type_label' => $this->order_type->label(),
            'visibility' => $this->visibility->value,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'is_open' => $this->status->isOpen(),
            'locked' => $locked,
            'entry_price' => $detail($this->entry_price),
            'stop_loss' => $detail($this->stop_loss),
            'take_profit' => $detail($this->take_profit),
            'rr' => $detail($this->rr),
            'sl_pips' => $detail($this->sl_pips),
            'analysis' => $detail($this->analysis),
            'chart_url' => $this->chart_image_path && ! $locked ? route('signals.chart', $this->id) : null,
            'close_price' => $this->close_price,
            'result_pips' => $this->result_pips,
            'result_r' => $this->result_r,
            'published_at' => $this->published_at->toIso8601String(),
            'triggered_at' => $this->triggered_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
        ];
    }
}
