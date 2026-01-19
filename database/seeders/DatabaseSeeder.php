<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\LeaveType;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Locations
        $locations = collect([
            ['name' => 'Head Office', 'code' => 'HO', 'city' => 'Delhi', 'state' => 'Delhi'],
            ['name' => 'Gurgaon Office', 'code' => 'GGN', 'city' => 'Gurgaon', 'state' => 'Haryana'],
            ['name' => 'Yeshwanthpur', 'code' => 'YLR', 'city' => 'Bangalore', 'state' => 'Karnataka'],
        ])->map(fn($loc) => Location::create([
                'name' => $loc['name'],
                'code' => $loc['code'],
                'address' => $loc['name'] . ' Address',
                'city' => $loc['city'],
                'state' => $loc['state'],
                'country' => 'India',
                'timezone' => 'Asia/Kolkata',
                'geo_fence_radius' => 100,
                'is_active' => true,
            ]));

        // Create Departments
        $departments = collect([
            'Administration',
            'Human Resources',
            'Finance',
            'Operations',
            'Sales',
            'IT',
        ])->map(fn($name) => Department::create([
                'name' => $name,
                'is_active' => true,
            ]));

        // Create Designations
        $designations = collect([
            ['name' => 'CEO', 'level' => 10],
            ['name' => 'Director', 'level' => 9],
            ['name' => 'Senior Manager', 'level' => 8],
            ['name' => 'Manager', 'level' => 7],
            ['name' => 'Team Lead', 'level' => 6],
            ['name' => 'Senior Executive', 'level' => 5],
            ['name' => 'Executive', 'level' => 4],
            ['name' => 'Junior Executive', 'level' => 3],
            ['name' => 'Trainee', 'level' => 2],
            ['name' => 'Intern', 'level' => 1],
        ])->map(fn($d) => Designation::create([
                'name' => $d['name'],
                'level' => $d['level'],
                'is_active' => true,
            ]));

        // Create Shifts
        $shifts = collect([
            [
                'name' => 'General Shift',
                'start_time' => '09:00',
                'end_time' => '18:00',
                'grace_period_minutes' => 15,
                'break_duration_minutes' => 60,
                'late_mark_after_minutes' => 15,
                'half_day_after_minutes' => 240,
                'min_working_hours' => 8,
                'min_half_day_hours' => 4,
                'is_flexible' => false,
                'is_night_shift' => false,
            ],
            [
                'name' => 'Morning Shift',
                'start_time' => '06:00',
                'end_time' => '14:00',
                'grace_period_minutes' => 10,
                'break_duration_minutes' => 30,
                'late_mark_after_minutes' => 10,
                'half_day_after_minutes' => 180,
                'min_working_hours' => 8,
                'min_half_day_hours' => 4,
                'is_flexible' => false,
                'is_night_shift' => false,
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '22:00',
                'end_time' => '06:00',
                'grace_period_minutes' => 15,
                'break_duration_minutes' => 60,
                'late_mark_after_minutes' => 15,
                'half_day_after_minutes' => 240,
                'min_working_hours' => 8,
                'min_half_day_hours' => 4,
                'is_flexible' => false,
                'is_night_shift' => true,
            ],
        ])->map(fn($s) => Shift::create($s));

        // Create Leave Types
        $leaveTypes = [
            [
                'name' => 'Casual Leave',
                'code' => 'CL',
                'total_days_per_year' => 12,
                'carry_forward' => false,
                'max_consecutive_days' => 3,
                'is_paid' => true,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'total_days_per_year' => 12,
                'carry_forward' => false,
                'requires_attachment' => true,
                'attachment_required_after_days' => 2,
                'max_consecutive_days' => 7,
                'is_paid' => true,
            ],
            [
                'name' => 'Earned Leave',
                'code' => 'EL',
                'total_days_per_year' => 15,
                'carry_forward' => true,
                'max_carry_forward_days' => 30,
                'max_consecutive_days' => 15,
                'is_paid' => true,
            ],
            [
                'name' => 'Leave Without Pay',
                'code' => 'LWP',
                'total_days_per_year' => 999,
                'carry_forward' => false,
                'is_paid' => false,
            ],
        ];

        foreach ($leaveTypes as $lt) {
            LeaveType::create(array_merge($lt, ['is_active' => true]));
        }

        // Create Super Admin User
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@apex.com',
            'password' => Hash::make('password'),
            'employee_id' => 'ADMIN001',
            'role' => 'super_admin',
            'department_id' => $departments->first()->id,
            'designation_id' => $designations->first()->id,
            'location_id' => $locations->first()->id,
            'shift_id' => $shifts->first()->id,
            'joining_date' => now()->subYears(5),
            'is_active' => true,
        ]);

        // Create HR Admin
        $hrAdmin = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@apex.com',
            'password' => Hash::make('password'),
            'employee_id' => 'HR001',
            'role' => 'hr_admin',
            'department_id' => $departments->where('name', 'Human Resources')->first()->id,
            'designation_id' => $designations->where('name', 'Manager')->first()->id,
            'location_id' => $locations->first()->id,
            'shift_id' => $shifts->first()->id,
            'joining_date' => now()->subYears(3),
            'is_active' => true,
        ]);

        // Create Manager
        $manager = User::create([
            'name' => 'John Manager',
            'email' => 'manager@apex.com',
            'password' => Hash::make('password'),
            'employee_id' => 'MGR001',
            'role' => 'manager',
            'department_id' => $departments->where('name', 'Operations')->first()->id,
            'designation_id' => $designations->where('name', 'Manager')->first()->id,
            'location_id' => $locations->first()->id,
            'shift_id' => $shifts->first()->id,
            'joining_date' => now()->subYears(2),
            'is_active' => true,
        ]);

        // Create Sample Employees
        $employees = [
            ['name' => 'Alice Employee', 'email' => 'alice@apex.com', 'employee_id' => 'EMP001'],
            ['name' => 'Bob Worker', 'email' => 'bob@apex.com', 'employee_id' => 'EMP002'],
            ['name' => 'Charlie Staff', 'email' => 'charlie@apex.com', 'employee_id' => 'EMP003'],
        ];

        foreach ($employees as $emp) {
            User::create([
                'name' => $emp['name'],
                'email' => $emp['email'],
                'password' => Hash::make('password'),
                'employee_id' => $emp['employee_id'],
                'role' => 'employee',
                'department_id' => $departments->where('name', 'Operations')->first()->id,
                'designation_id' => $designations->where('name', 'Executive')->first()->id,
                'location_id' => $locations->first()->id,
                'shift_id' => $shifts->first()->id,
                'manager_id' => $manager->id,
                'joining_date' => now()->subYear(),
                'is_active' => true,
            ]);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('  Super Admin: admin@apex.com / password');
        $this->command->info('  HR Admin: hr@apex.com / password');
        $this->command->info('  Manager: manager@apex.com / password');
        $this->command->info('  Employee: alice@apex.com / password');
    }
}
