<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['booking_id', 'rating', 'comment'])]
class Review extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Review $review): void {
            $review->created_at ??= now();
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
