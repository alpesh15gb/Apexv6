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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->year('year');

            $table->decimal('opening_balance', 5, 1)->default(0);
            $table->decimal('accrued', 5, 1)->default(0);
            $table->decimal('used', 5, 1)->default(0);
            $table->decimal('pending', 5, 1)->default(0)->comment('Applied but not approved');
            $table->decimal('carry_forward', 5, 1)->default(0);
            $table->decimal('adjustment', 5, 1)->default(0)->comment('Manual adjustments');

            $table->timestamps();

            $table->unique(['user_id', 'leave_type_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
