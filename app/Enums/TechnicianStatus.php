<?php

namespace App\Enums;

enum TechnicianStatus: string
{
    case Available = 'available';
    case Busy = 'busy';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Busy => 'Busy',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Busy => 'secondary',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return [
            self::Available->value => self::Available->label(),
            self::Busy->value => self::Busy->label(),
        ];
    }
}
