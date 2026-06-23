<?php

namespace App\Enums;

enum UserRole: string
{
    case Laborer = 'laborer';
    case Contractor = 'contractor';
    case Subcontractor = 'subcontractor';
    case Apprentice = 'apprentice';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
