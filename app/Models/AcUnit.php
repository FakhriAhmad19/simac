<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['customer_id', 'brand', 'type', 'capacity_pk', 'location_note'])]
class AcUnit extends Model
{
    /** @use HasFactory<\Database\Factories\AcUnitFactory> */
    use HasFactory;

    protected $table = 'ac_units';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function label(): string
    {
        return trim(collect([$this->brand, $this->type, $this->capacity_pk])
            ->filter()
            ->implode(' · ')) ?: 'Unit #'.$this->id;
    }
}
