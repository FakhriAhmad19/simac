<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('technician_id')->nullable()->constrained('technicians')->nullOnDelete();
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('ac_unit_id')->nullable()->constrained('ac_units')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->enum('status', [
                'pending',
                'assigned',
                'on_the_way',
                'in_progress',
                'completed',
                'cancelled',
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
