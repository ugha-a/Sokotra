<?php

namespace App\Policies;

use App\Enums\Role;
use App\Enums\SignalVisibility;
use App\Models\Signal;
use App\Models\Subscription;
use App\Models\User;

class SignalPolicy
{
    /**
     * Full levels, analysis and chart (docs/logic/signal-lifecycle.md → Visibilitas).
     */
    public function viewDetails(?User $user, Signal $signal): bool
    {
        return $signal->visibility === SignalVisibility::Free
            || ! $signal->status->isOpen()
            || $user?->id === $signal->trader_id
            || ($user?->hasRole(Role::Admin->value) ?? false)
            || Subscription::isActive($user, $signal->trader_id);
    }

    public function cancel(User $user, Signal $signal): bool
    {
        return $user->id === $signal->trader_id;
    }
}
