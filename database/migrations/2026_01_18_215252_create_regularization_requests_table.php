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
        Schema::create('regularization_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('punch_in_time')->nullable();
            $table->time('punch_out_time')->nullable();
            $table->text('reason'); // Why they need regularization
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->text('remarks')->nullable(); // Approver remarks
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regularization_requests');
    }
};
