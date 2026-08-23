<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Transfer => 'Transfer',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return [
            self::Cash->value => self::Cash->label(),
            self::Transfer->value => self::Transfer->label(),
        ];
    }
}
