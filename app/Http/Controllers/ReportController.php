<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfMonth();
        $to = $to->copy()->endOfDay();

        $bookingsInRange = Booking::whereBetween('scheduled_at', [$from, $to]);

        $statusCounts = (clone $bookingsInRange)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $revenue = Payment::where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount');

        $technicianPerformance = Technician::with('user')
            ->withCount([
                'bookings as completed_count' => fn ($q) => $q
                    ->where('status', BookingStatus::Completed)
                    ->whereBetween('scheduled_at', [$from, $to]),
                'bookings as total_count' => fn ($q) => $q
                    ->whereBetween('scheduled_at', [$from, $to]),
            ])
            ->orderByDesc('completed_count')
            ->get();

        return view('reports.index', [
            'from' => $from,
            'to' => $to,
            'statuses' => BookingStatus::options(),
            'statusCounts' => $statusCounts,
            'totalBookings' => (clone $bookingsInRange)->count(),
            'revenue' => $revenue,
            'unpaidTotal' => Payment::where('status', PaymentStatus::Unpaid)
                ->whereHas('booking', fn ($q) => $q->whereBetween('scheduled_at', [$from, $to]))
                ->sum('amount'),
            'technicianPerformance' => $technicianPerformance,
        ]);
    }
}
