<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\TechnicianStatus;
use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * Create a booking and record its initial history entry.
     *
     * @param  array<string,mixed>  $data
     */
    public function create(array $data, User $actor): Booking
    {
        return DB::transaction(function () use ($data, $actor) {
            $data['created_by'] = $actor->id;
            $data['status'] = BookingStatus::Pending;

            $booking = Booking::create($data);

            $this->log($booking, BookingStatus::Pending, $actor, 'Booking dibuat.');

            return $booking;
        });
    }

    /**
     * Assign a technician to a booking. Marks the technician busy and moves
     * the booking to "assigned". Wrapped in a transaction for consistency.
     */
    public function assignTechnician(Booking $booking, Technician $technician, User $actor, ?string $notes = null): Booking
    {
        if (in_array($booking->status, [BookingStatus::Completed, BookingStatus::Cancelled], true)) {
            throw ValidationException::withMessages([
                'technician_id' => 'Booking yang sudah selesai atau dibatalkan tidak dapat ditugaskan.',
            ]);
        }

        return DB::transaction(function () use ($booking, $technician, $actor, $notes) {
            $booking->update([
                'technician_id' => $technician->id,
                'status' => BookingStatus::Assigned,
            ]);

            $technician->update(['status' => TechnicianStatus::Busy]);

            $this->log(
                $booking,
                BookingStatus::Assigned,
                $actor,
                $notes ?? "Ditugaskan ke {$technician->user->name}.",
            );

            return $booking->refresh();
        });
    }

    /**
     * Move a booking to a new status, recording history. Frees the technician
     * when the job reaches a terminal state (completed/cancelled).
     */
    public function changeStatus(Booking $booking, BookingStatus $to, User $actor, ?string $notes = null): Booking
    {
        return DB::transaction(function () use ($booking, $to, $actor, $notes) {
            $booking->update(['status' => $to]);

            if (in_array($to, [BookingStatus::Completed, BookingStatus::Cancelled], true)
                && $booking->technician) {
                $booking->technician->update(['status' => TechnicianStatus::Available]);
            }

            $this->log($booking, $to, $actor, $notes);

            return $booking->refresh();
        });
    }

    private function log(Booking $booking, BookingStatus $status, User $actor, ?string $notes = null): void
    {
        BookingHistory::create([
            'booking_id' => $booking->id,
            'status' => $status,
            'changed_by' => $actor->id,
            'notes' => $notes,
        ]);
    }
}
