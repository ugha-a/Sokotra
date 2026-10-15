<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\ConsentType;
use App\Enums\Role;
use App\Models\ContentPage;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'accept_terms' => ['accepted'],
        ], ['accept_terms.accepted' => 'Kamu perlu menyetujui syarat & ketentuan dan disclaimer risiko.'])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        $user->forceFill(['registration_ip' => request()->ip()])->save();
        $user->assignRole(Role::Member->value);

        foreach ([ConsentType::Terms->value => 'syarat-ketentuan', ConsentType::RiskDisclaimer->value => 'disclaimer-risiko'] as $type => $slug) {
            $user->consents()->create([
                'type' => $type,
                'version' => ContentPage::where('slug', $slug)->first()?->updated_at?->format('YmdHis') ?? 'unversioned',
                'accepted_at' => now(),
                'ip' => request()->ip(),
            ]);
        }

        return $user;
    }
}
