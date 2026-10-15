<?php

namespace App\Policies;

use App\Enums\Role;
use App\Enums\SignalVisibility;
use App\Models\Signal;
use App\Models\User;

class SignalPolicy
{
    /**
     * Full levels, analysis and chart (docs/logic/signal-lifecycle.md → Visibilitas).
     * Active subscribers are added with the subscription module.
     */
    public function viewDetails(?User $user, Signal $signal): bool
    {
        return $signal->visibility === SignalVisibility::Free
            || ! $signal->status->isOpen()
            || $user?->id === $signal->trader_id
            || ($user?->hasRole(Role::Admin->value) ?? false);
    }

    public function cancel(User $user, Signal $signal): bool
    {
        return $user->id === $signal->trader_id;
    }
}
