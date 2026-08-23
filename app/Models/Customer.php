<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone', 'address', 'created_by'])]
class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acUnits(): HasMany
    {
        return $this->hasMany(AcUnit::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Phone number normalised to international format for wa.me (e.g. 62812...).
     */
    public function waNumber(): string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }
        if (str_starts_with($digits, '62')) {
            return $digits;
        }
        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    /**
     * Build a WhatsApp click-to-chat link, optionally with a prefilled message.
     */
    public function waUrl(string $message = ''): string
    {
        $url = 'https://wa.me/'.$this->waNumber();

        return $message !== '' ? $url.'?text='.rawurlencode($message) : $url;
    }
}
