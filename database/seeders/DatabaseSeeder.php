<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TechnicianStatus;
use App\Models\AcUnit;
use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Service;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Core accounts ---------------------------------------------------
        $admin = User::create([
            'name' => 'Admin SIMAC',
            'email' => 'admin@simac.test',
            'phone' => '081200000001',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Owner SIMAC',
            'email' => 'owner@simac.test',
            'phone' => '081200000002',
            'role' => 'owner',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $technicians = collect(['Budi Santoso', 'Andi Wijaya', 'Rudi Hartono'])
            ->map(function (string $name, int $i) {
                $user = User::create([
                    'name' => $name,
                    'email' => 'teknisi'.($i + 1).'@simac.test',
                    'phone' => '08130000000'.($i + 1),
                    'role' => 'technician',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);

                return Technician::create([
                    'user_id' => $user->id,
                    'specialization' => ['Servis rutin', 'Bongkar-pasang', 'Kelistrikan'][$i],
                    'status' => TechnicianStatus::Available,
                ]);
            });

        // --- Services --------------------------------------------------------
        $services = collect([
            ['Cuci AC', 'Pembersihan unit indoor & outdoor', 75000, 45],
            ['Servis Rutin', 'Pemeriksaan dan perawatan berkala', 120000, 60],
            ['Isi Freon', 'Pengisian ulang freon R32/R410', 250000, 60],
            ['Bongkar Pasang', 'Pemindahan unit AC', 350000, 120],
            ['Perbaikan', 'Perbaikan kerusakan umum', 200000, 90],
        ])->map(fn ($s) => Service::create([
            'name' => $s[0], 'description' => $s[1], 'price' => $s[2], 'estimated_duration' => $s[3],
        ]));

        // --- Customers + units ----------------------------------------------
        $customers = Customer::factory(8)
            ->create(['created_by' => $admin->id])
            ->each(fn (Customer $c) => AcUnit::factory(rand(1, 2))->create(['customer_id' => $c->id]));

        // --- Bookings with realistic status progression ----------------------
        $statuses = [
            BookingStatus::Pending,
            BookingStatus::Assigned,
            BookingStatus::OnTheWay,
            BookingStatus::InProgress,
            BookingStatus::Completed,
            BookingStatus::Completed,
            BookingStatus::Cancelled,
        ];

        foreach (range(1, 18) as $i) {
            $customer = $customers->random();
            $unit = $customer->acUnits->first();
            $status = $statuses[array_rand($statuses)];
            $needsTechnician = $status !== BookingStatus::Pending && $status !== BookingStatus::Cancelled;
            $technician = $needsTechnician ? $technicians->random() : null;

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'technician_id' => $technician?->id,
                'service_id' => $services->random()->id,
                'ac_unit_id' => $unit?->id,
                'scheduled_at' => now()->subDays(rand(-7, 30))->setTime(rand(8, 16), 0),
                'status' => $status,
                'notes' => fake()->optional()->sentence(),
                'created_by' => $admin->id,
            ]);

            $this->buildHistory($booking, $status, $admin->id, $technician?->user_id ?? $admin->id);

            if ($status === BookingStatus::Completed) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $booking->service->price,
                    'payment_method' => fake()->randomElement(PaymentMethod::cases()),
                    'status' => PaymentStatus::Paid,
                    'paid_at' => $booking->scheduled_at,
                ]);

                if (fake()->boolean(70)) {
                    Review::create([
                        'booking_id' => $booking->id,
                        'rating' => rand(4, 5),
                        'comment' => fake()->optional()->sentence(),
                        'created_at' => $booking->scheduled_at,
                    ]);
                }
            }
        }
    }

    /**
     * Write a plausible chain of history rows up to the booking's current status.
     */
    private function buildHistory(Booking $booking, BookingStatus $current, int $adminId, int $techId): void
    {
        $flow = [
            BookingStatus::Pending,
            BookingStatus::Assigned,
            BookingStatus::OnTheWay,
            BookingStatus::InProgress,
            BookingStatus::Completed,
        ];

        if ($current === BookingStatus::Cancelled) {
            BookingHistory::create(['booking_id' => $booking->id, 'status' => BookingStatus::Pending, 'changed_by' => $adminId, 'notes' => 'Booking dibuat.', 'created_at' => $booking->created_at]);
            BookingHistory::create(['booking_id' => $booking->id, 'status' => BookingStatus::Cancelled, 'changed_by' => $adminId, 'notes' => 'Dibatalkan oleh admin.', 'created_at' => $booking->created_at]);

            return;
        }

        foreach ($flow as $step) {
            $by = $step === BookingStatus::Pending || $step === BookingStatus::Assigned ? $adminId : $techId;
            BookingHistory::create([
                'booking_id' => $booking->id,
                'status' => $step,
                'changed_by' => $by,
                'notes' => null,
                'created_at' => $booking->created_at,
            ]);

            if ($step === $current) {
                break;
            }
        }
    }
}
