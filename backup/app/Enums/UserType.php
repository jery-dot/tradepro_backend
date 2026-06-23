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

    // 🔥 Ajout du mapping des classes CSS (Badges)
    public function badgeClass(): string
    {
        return match ($this) {
            self::CONTRACTOR    => 'bn',
            self::SUBCONTRACTOR => 'bo',
            self::LABORER       => 'bg',
            self::APPRENTICE    => 'bp',
        };
    }

    // 🔥 Ajout du mapping des couleurs hexadécimales
    public function color(): string
    {
        return match ($this) {
            self::CONTRACTOR    => '#1B3D6F',
            self::SUBCONTRACTOR => '#F5874F',
            self::LABORER       => '#27AE60',
            self::APPRENTICE    => '#8E44AD',
        };
    }
}
