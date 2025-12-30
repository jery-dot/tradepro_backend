<?php

namespace App\Enums;

enum UserType: int
{
    case CONTRACTOR    = 0;
    case SUBCONTRACTOR = 1;
    case LABORER       = 2;
    case APPRENTICE    = 3;

    public function label(): string
    {
        return match ($this) {
            self::CONTRACTOR    => 'Contractor',
            self::SUBCONTRACTOR => 'Subcontractor',
            self::LABORER       => 'Laborer',
            self::APPRENTICE    => 'Apprentice',
        };
    }

    /**
     * Return all numeric values as array, useful for validation.
     */
    public static function values(): array
    {
        return array_map(
            fn (self $case) => $case->value,
            self::cases()
        );
    }
}
