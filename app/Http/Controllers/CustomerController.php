<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();

        $customers = Customer::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->withCount(['acUnits', 'bookings'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;

        $customer = Customer::create($data);

        // When the user came from the "Buat Booking" page, return there with the
        // newly created customer pre-selected so they can continue the booking.
        if ($request->input('from') === 'booking') {
            return redirect()->route('bookings.create', ['customer_id' => $customer->id])
                ->with('success', "Customer \"{$customer->name}\" ditambahkan. Silakan lanjutkan booking.");
        }

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'acUnits',
            'bookings' => fn ($q) => $q->with(['service', 'technician.user'])->latest('scheduled_at'),
            'creator',
        ]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validateData($request));

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Data customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->bookings()->exists()) {
            return back()->with('error', 'Customer dengan riwayat booking tidak dapat dihapus.');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer berhasil dihapus.');
    }

    /** @return array<string,mixed> */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
