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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('grace_period_minutes')->default(15);
            $table->integer('break_duration_minutes')->default(60);
            $table->integer('late_mark_after_minutes')->default(15)->comment('Mark late after grace + these minutes');
            $table->integer('half_day_after_minutes')->default(240)->comment('Mark half day if late by this much');
            $table->integer('min_working_hours')->default(8)->comment('Minimum hours for full day');
            $table->integer('min_half_day_hours')->default(4)->comment('Minimum hours for half day');
            $table->boolean('is_flexible')->default(false);
            $table->boolean('is_night_shift')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
