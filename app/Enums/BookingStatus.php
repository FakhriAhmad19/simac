<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case OnTheWay = 'on_the_way';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Assigned => 'Assigned',
            self::OnTheWay => 'On The Way',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /** Bootstrap badge color. */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::Assigned => 'info',
            self::OnTheWay => 'primary',
            self::InProgress => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    /**
     * Statuses a technician may advance an assigned booking to (forward-only).
     *
     * @return array<int,self>
     */
    public function technicianNextOptions(): array
    {
        return match ($this) {
            self::Assigned => [self::OnTheWay],
            self::OnTheWay => [self::InProgress],
            self::InProgress => [self::Completed],
            default => [],
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $case) => $carry + [$case->value => $case->label()],
            [],
        );
    }
}
