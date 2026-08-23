<?php

namespace App\Models;

use App\Enums\TechnicianStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'specialization', 'status'])]
class Technician extends Model
{
    /** @use HasFactory<\Database\Factories\TechnicianFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => TechnicianStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === TechnicianStatus::Available;
    }
}
