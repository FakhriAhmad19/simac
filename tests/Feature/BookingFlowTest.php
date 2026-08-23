<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\TechnicianStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Technician;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_booking_with_pending_status_and_history(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = Customer::factory()->create(['created_by' => $admin->id]);
        $service = Service::factory()->create();

        $this->actingAs($admin)->post('/bookings', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'notes' => 'Test booking',
        ])->assertRedirect();

        $booking = Booking::first();
        $this->assertNotNull($booking);
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame(1, $booking->histories()->count());
    }

    public function test_service_assigns_technician_and_marks_busy(): void
    {
        $admin = User::factory()->admin()->create();
        $technician = Technician::factory()->create(['status' => TechnicianStatus::Available]);
        $booking = Booking::factory()->create(['created_by' => $admin->id]);

        app(BookingService::class)->assignTechnician($booking, $technician, $admin);

        $this->assertSame(BookingStatus::Assigned, $booking->refresh()->status);
        $this->assertSame(TechnicianStatus::Busy, $technician->refresh()->status);
    }

    public function test_completing_a_booking_frees_the_technician(): void
    {
        $admin = User::factory()->admin()->create();
        $technician = Technician::factory()->create(['status' => TechnicianStatus::Available]);
        $booking = Booking::factory()->create(['created_by' => $admin->id]);
        $service = app(BookingService::class);

        $service->assignTechnician($booking, $technician, $admin);
        $service->changeStatus($booking, BookingStatus::OnTheWay, $admin);
        $service->changeStatus($booking, BookingStatus::InProgress, $admin);
        $service->changeStatus($booking, BookingStatus::Completed, $admin);

        $this->assertSame(BookingStatus::Completed, $booking->refresh()->status);
        $this->assertSame(TechnicianStatus::Available, $technician->refresh()->status);
        // assigned + on_the_way + in_progress + completed (factory booking has no initial pending log)
        $this->assertSame(4, $booking->histories()->count());
    }

    public function test_technician_can_only_progress_their_own_booking(): void
    {
        $admin = User::factory()->admin()->create();
        $ownTech = Technician::factory()->create();
        $otherTech = Technician::factory()->create();
        $booking = Booking::factory()->create([
            'created_by' => $admin->id,
            'technician_id' => $ownTech->id,
            'status' => BookingStatus::Assigned,
        ]);

        // Foreign technician is forbidden.
        $this->actingAs($otherTech->user)
            ->patch("/bookings/{$booking->id}/status", ['status' => 'on_the_way'])
            ->assertForbidden();

        // Owning technician may advance one step forward.
        $this->actingAs($ownTech->user)
            ->patch("/bookings/{$booking->id}/status", ['status' => 'on_the_way'])
            ->assertRedirect();
        $this->assertSame(BookingStatus::OnTheWay, $booking->refresh()->status);

        // Skipping a step is rejected.
        $this->actingAs($ownTech->user)
            ->patch("/bookings/{$booking->id}/status", ['status' => 'completed'])
            ->assertSessionHasErrors('status');
    }

    public function test_payment_and_review_require_completed_status(): void
    {
        $admin = User::factory()->admin()->create();
        $booking = Booking::factory()->create([
            'created_by' => $admin->id,
            'status' => BookingStatus::Completed,
        ]);

        $this->actingAs($admin)->post("/bookings/{$booking->id}/payment", [
            'amount' => 150000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ])->assertRedirect();

        $this->assertDatabaseHas('payments', ['booking_id' => $booking->id, 'status' => 'paid']);

        $this->actingAs($admin)->post("/bookings/{$booking->id}/review", [
            'rating' => 5,
            'comment' => 'Mantap',
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', ['booking_id' => $booking->id, 'rating' => 5]);
    }
}
