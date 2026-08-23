<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->status !== BookingStatus::Completed) {
            return back()->with('error', 'Ulasan hanya dapat dicatat untuk booking yang sudah completed.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        $booking->review()->updateOrCreate(
            ['booking_id' => $booking->id],
            $data,
        );

        return back()->with('success', 'Ulasan berhasil dicatat.');
    }
}
