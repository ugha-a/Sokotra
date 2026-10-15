<?php

namespace App\Enums;

/**
 * Fine-grained permissions on top of roles (docs/logic/compliance.md).
 */
enum Permission: string
{
    case KycReview = 'kyc.review';

    /**
     * Roles that receive this permission by default.
     *
     * @return list<Role>
     */
    public function defaultRoles(): array
    {
        return match ($this) {
            self::KycReview => [Role::Admin],
        };
    }
}
