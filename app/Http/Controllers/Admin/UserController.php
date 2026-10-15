<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Audit\RecordAdminAudit;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? null;

        return Inertia::render('admin/users/index', [
            'search' => $search,
            'users' => User::query()
                ->with(['roles:id,name', 'traderProfile:id,user_id,status,display_name'])
                ->when($search, fn (Builder $query, string $term) => $query->where(fn (Builder $q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")))
                ->latest('id')
                ->paginate(25)
                ->withQueryString()
                ->through(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->values()->all(),
                    'trader_status' => $user->traderProfile?->status->label(),
                    'is_fraud_flagged' => $user->is_fraud_flagged,
                    'email_verified' => $user->email_verified_at !== null,
                    'last_active_at' => $user->last_active_at?->toIso8601String(),
                    'created_at' => $user->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function flag(Request $request, User $user, RecordAdminAudit $audit): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'max:255']], [], ['reason' => 'alasan'])['reason'];
        $user->forceFill(['is_fraud_flagged' => true])->save();
        $audit->handle($this->admin($request), 'user.flagged', $user, ['is_fraud_flagged' => false], ['is_fraud_flagged' => true, 'reason' => $reason]);

        return back();
    }

    public function unflag(Request $request, User $user, RecordAdminAudit $audit): RedirectResponse
    {
        $user->forceFill(['is_fraud_flagged' => false])->save();
        $audit->handle($this->admin($request), 'user.unflagged', $user, ['is_fraud_flagged' => true], ['is_fraud_flagged' => false]);

        return back();
    }

    private function admin(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
