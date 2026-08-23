<?php

namespace App\Http\Controllers;

use App\Models\AcUnit;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcUnitController extends Controller
{
    public function create(Customer $customer): View
    {
        return view('ac_units.create', compact('customer'));
    }

    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $acUnit = $customer->acUnits()->create($this->validateData($request));

        // When adding the unit mid-booking, return to "Buat Booking" with the
        // customer and this new unit pre-selected so the flow can continue.
        if ($request->input('from') === 'booking') {
            return redirect()->route('bookings.create', [
                'customer_id' => $customer->id,
                'ac_unit_id' => $acUnit->id,
            ])->with('success', 'Unit AC ditambahkan. Silakan lanjutkan booking.');
        }

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Unit AC berhasil ditambahkan.');
    }

    public function edit(AcUnit $acUnit): View
    {
        $acUnit->load('customer');

        return view('ac_units.edit', compact('acUnit'));
    }

    public function update(Request $request, AcUnit $acUnit): RedirectResponse
    {
        $acUnit->update($this->validateData($request));

        return redirect()->route('customers.show', $acUnit->customer_id)
            ->with('success', 'Unit AC berhasil diperbarui.');
    }

    public function destroy(AcUnit $acUnit): RedirectResponse
    {
        $customerId = $acUnit->customer_id;
        $acUnit->delete();

        return redirect()->route('customers.show', $customerId)
            ->with('success', 'Unit AC berhasil dihapus.');
    }

    /** @return array<string,mixed> */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'brand' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'capacity_pk' => ['nullable', 'string', 'max:50'],
            'location_note' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
