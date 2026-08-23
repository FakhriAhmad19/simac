<?php

namespace App\Http\Controllers;

use App\Enums\TechnicianStatus;
use App\Models\Technician;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TechnicianController extends Controller
{
    public function index(): View
    {
        $technicians = Technician::with('user')
            ->withCount(['bookings as active_count' => fn ($q) => $q
                ->whereNotIn('status', ['completed', 'cancelled'])])
            ->get()
            ->sortBy('user.name')
            ->values();

        return view('technicians.index', compact('technicians'));
    }

    public function updateStatus(Request $request, Technician $technician): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(TechnicianStatus::class)],
        ]);

        $technician->update(['status' => $data['status']]);

        return back()->with('success', 'Status teknisi diperbarui.');
    }
}
