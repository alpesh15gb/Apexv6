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
        Schema::table('users', function (Blueprint $table) {
            // Employee identification
            $table->string('employee_id', 20)->unique()->nullable();

            // Organizational relations
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('manager_id')->nullable();

            // Personal info
            $table->date('date_of_birth')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('gender', 10)->nullable(); // male, female, other

            // Role and status
            $table->string('role', 20)->default('employee'); // super_admin, hr_admin, manager, employee
            $table->boolean('is_active')->default(true);

            // Device mapping
            $table->string('device_employee_id', 50)->nullable();

            // Soft deletes
            $table->softDeletes();

            // Foreign key for self-referencing manager
            $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['designation_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['shift_id']);

            $table->dropColumn([
                'employee_id',
                'department_id',
                'designation_id',
                'location_id',
                'shift_id',
                'manager_id',
                'date_of_birth',
                'joining_date',
                'phone',
                'address',
                'profile_photo',
                'gender',
                'role',
                'is_active',
                'device_employee_id',
                'deleted_at'
            ]);
        });
    }
};
