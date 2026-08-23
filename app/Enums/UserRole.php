<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Owner = 'owner';
    case Technician = 'technician';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Owner => 'Owner/Manager',
            self::Technician => 'Teknisi',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return [
            self::Admin->value => self::Admin->label(),
            self::Owner->value => self::Owner->label(),
            self::Technician->value => self::Technician->label(),
        ];
    }
}
