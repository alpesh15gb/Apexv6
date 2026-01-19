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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();

            // Date range
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('total_days', 4, 1)->comment('Can be 0.5 for half days');

            // Half day details
            $table->boolean('is_half_day')->default(false);
            $table->string('half_day_type', 15)->nullable(); // first_half, second_half

            // Request details
            $table->text('reason');
            $table->string('attachment')->nullable();

            // Status
            $table->string('status', 15)->default('pending'); // pending, approved, rejected, cancelled
            $table->timestamp('applied_at')->useCurrent();

            // Approval
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['from_date', 'to_date']);

            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
