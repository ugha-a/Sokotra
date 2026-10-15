<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Published = 'published';
    case Rejected = 'rejected';
    case Unlisted = 'unlisted';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InReview => 'Menunggu Review',
            self::Published => 'Tayang',
            self::Rejected => 'Ditolak',
            self::Unlisted => 'Disembunyikan',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $case) => ['value' => $case->value, 'label' => $case->label()], self::cases());
    }
}
