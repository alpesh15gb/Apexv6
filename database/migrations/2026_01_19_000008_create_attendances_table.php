<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');

            // Punch in details
            $table->time('punch_in_time')->nullable();
            $table->decimal('punch_in_latitude', 10, 8)->nullable();
            $table->decimal('punch_in_longitude', 11, 8)->nullable();
            $table->string('punch_in_photo')->nullable();
            $table->string('punch_in_device')->nullable()->comment('Device identifier for sync');
            $table->string('punch_in_ip')->nullable();

            // Punch out details
            $table->time('punch_out_time')->nullable();
            $table->decimal('punch_out_latitude', 10, 8)->nullable();
            $table->decimal('punch_out_longitude', 11, 8)->nullable();
            $table->string('punch_out_photo')->nullable();
            $table->string('punch_out_device')->nullable();
            $table->string('punch_out_ip')->nullable();

            // Calculated fields
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->integer('break_duration_minutes')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_departure_minutes')->default(0);

            // Status (present, absent, half_day, late, leave, holiday, week_off, on_duty)
            $table->string('status', 20)->default('absent');

            // Approval
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Sync flags
            $table->boolean('synced_from_device')->default(false);
            $table->string('raw_punch_data')->nullable()->comment('Original data from device for audit');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['user_id', 'date']);
            $table->index(['date', 'status']);
            $table->index('synced_from_device');

            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
