<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\AcUnit;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return match (true) {
            $user->isOwner() => $this->owner(),
            $user->isTechnician() => $this->technician($request),
            default => $this->admin(),
        };
    }

    private function admin(): View
    {
        $statusCounts = Booking::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // AC units whose most recent completed service is older than the interval —
        // these are "due" for a routine maintenance reminder.
        $serviceIntervalDays = 90;
        $dueThreshold = now()->subDays($serviceIntervalDays);

        $dueUnitsQuery = AcUnit::query()
            ->with('customer')
            ->select('ac_units.*')
            ->selectSub(
                Booking::selectRaw('MAX(scheduled_at)')
                    ->whereColumn('ac_unit_id', 'ac_units.id')
                    ->where('status', BookingStatus::Completed),
                'last_service_at'
            )
            ->having('last_service_at', '<', $dueThreshold->toDateTimeString())
            ->orderBy('last_service_at');

        return view('dashboard.admin', [
            'statusCounts' => $statusCounts,
            'totalCustomers' => Customer::count(),
            'availableTechnicians' => Technician::where('status', 'available')->count(),
            'todayBookings' => Booking::whereDate('scheduled_at', today())
                ->with(['customer', 'service', 'technician.user'])
                ->orderBy('scheduled_at')
                ->get(),
            'serviceIntervalDays' => $serviceIntervalDays,
            'dueUnits' => (clone $dueUnitsQuery)->limit(8)->get(),
            'dueUnitsCount' => (clone $dueUnitsQuery)->get()->count(),
        ]);
    }

    private function owner(): View
    {
        $period = collect(range(0, 5))->map(function (int $monthsAgo) {
            $month = now()->startOfMonth()->subMonths($monthsAgo);

            $revenue = Payment::where('status', PaymentStatus::Paid)
                ->whereBetween('paid_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount');

            return [
                'label' => $month->translatedFormat('M Y'),
                'revenue' => (float) $revenue,
            ];
        })->reverse()->values();

        $technicianPerformance = Technician::with('user')
            ->withCount(['bookings as completed_count' => fn ($q) => $q->where('status', BookingStatus::Completed)])
            ->orderByDesc('completed_count')
            ->get();

        return view('dashboard.owner', [
            'statusCounts' => Booking::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'monthlyRevenue' => $period,
            'totalRevenue' => Payment::where('status', PaymentStatus::Paid)->sum('amount'),
            'unpaidTotal' => Payment::where('status', PaymentStatus::Unpaid)->sum('amount'),
            'technicianPerformance' => $technicianPerformance,
        ]);
    }

    private function technician(Request $request): View
    {
        $technician = $request->user()->technician;

        $active = collect();
        $done = collect();

        if ($technician) {
            $active = Booking::where('technician_id', $technician->id)
                ->whereNotIn('status', [BookingStatus::Completed, BookingStatus::Cancelled])
                ->with(['customer', 'service', 'acUnit'])
                ->orderBy('scheduled_at')
                ->get();

            $done = Booking::where('technician_id', $technician->id)
                ->where('status', BookingStatus::Completed)
                ->with(['customer', 'service'])
                ->latest('updated_at')
                ->limit(20)
                ->get();
        }

        return view('dashboard.technician', [
            'technician' => $technician,
            'activeBookings' => $active,
            'completedBookings' => $done,
        ]);
    }
}
