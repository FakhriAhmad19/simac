<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\AcUnit;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Technician;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->value();
        $search = trim($request->string('q')->value());

        $bookings = Booking::query()
            ->with(['customer', 'service', 'technician.user'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('id', ltrim($search, '#'))
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"))
                        ->orWhereHas('service', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('technician.user', fn ($t) => $t->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        return view('bookings.index', [
            'bookings' => $bookings,
            'status' => $status,
            'search' => $search,
            'statuses' => BookingStatus::options(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('bookings.create', [
            'customers' => Customer::orderBy('name')->get(),
            'services' => Service::orderBy('name')->get(),
            'selectedCustomer' => $request->integer('customer_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'service_id' => ['required', 'exists:services,id'],
            'ac_unit_id' => [
                'nullable',
                Rule::exists('ac_units', 'id')->where('customer_id', $request->integer('customer_id')),
            ],
            'scheduled_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $booking = $this->bookings->create($data, $request->user());

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking berhasil dibuat dengan status pending.');
    }

    public function show(Booking $booking): View
    {
        $booking->load([
            'customer',
            'service',
            'acUnit',
            'technician.user',
            'creator',
            'histories.changedBy',
            'payment',
            'review',
        ]);

        return view('bookings.show', [
            'booking' => $booking,
            'availableTechnicians' => Technician::with('user')
                ->where('status', 'available')
                ->get(),
        ]);
    }

    public function assign(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'technician_id' => ['required', 'exists:technicians,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $technician = Technician::findOrFail($data['technician_id']);

        $this->bookings->assignTechnician($booking, $technician, $request->user(), $data['notes'] ?? null);

        return back()->with('success', 'Teknisi berhasil ditugaskan.');
    }

    /**
     * Technician-driven forward status update (on_the_way, in_progress, completed).
     */
    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();

        // Technicians may only progress their own bookings.
        if ($user->isTechnician() && $booking->technician?->user_id !== $user->id) {
            abort(403, 'Anda hanya dapat memperbarui booking yang ditugaskan kepada Anda.');
        }

        $data = $request->validate([
            'status' => ['required', Rule::enum(BookingStatus::class)],
            'notes' => ['nullable', 'string'],
        ]);

        $target = BookingStatus::from($data['status']);
        $allowed = $booking->status->technicianNextOptions();

        if (! in_array($target, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => 'Perubahan status tidak valid dari '.$booking->status->label().'.',
            ]);
        }

        $this->bookings->changeStatus($booking, $target, $user, $data['notes'] ?? null);

        return back()->with('success', 'Status booking diperbarui menjadi '.$target->label().'.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        if (in_array($booking->status, [BookingStatus::Completed, BookingStatus::Cancelled], true)) {
            return back()->with('error', 'Booking ini tidak dapat dibatalkan.');
        }

        $data = $request->validate(['notes' => ['nullable', 'string']]);

        $this->bookings->changeStatus(
            $booking,
            BookingStatus::Cancelled,
            $request->user(),
            $data['notes'] ?? 'Dibatalkan oleh admin.',
        );

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }

    /** Return AC units for a customer as JSON (used by the booking form). */
    public function unitsForCustomer(Customer $customer): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            $customer->acUnits()->get()->map(fn (AcUnit $u) => [
                'id' => $u->id,
                'label' => $u->label(),
            ])
        );
    }
}
