<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->status !== BookingStatus::Completed) {
            return back()->with('error', 'Pembayaran hanya dapat dicatat untuk booking yang sudah completed.');
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'status' => ['required', Rule::enum(PaymentStatus::class)],
        ]);

        $data['paid_at'] = $data['status'] === PaymentStatus::Paid->value ? now() : null;

        $booking->payment()->updateOrCreate(
            ['booking_id' => $booking->id],
            $data,
        );

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }
}
