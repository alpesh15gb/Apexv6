<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function __construct()
    {
        // Simple admin check via middleware-style approach
    }

    /**
     * Check if user has admin access
     */
    private function checkAdminAccess(): void
    {
        if (!Auth::user()?->hasAdminAccess()) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Display a listing of employees
     */
    public function index(Request $request)
    {
        $this->checkAdminAccess();

        $query = User::with(['department', 'designation', 'location', 'shift'])
            ->where('role', '!=', 'super_admin');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Filter by Department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by location
        if ($locationId = $request->input('location_id')) {
            $query->where('location_id', $locationId);
        }

        // Filter by status
        if ($request->input('status') === 'active') {
            $query->active();
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $employees = $query->orderBy('name')->paginate(15)->withQueryString();

        $departments = Department::active()->get();
        $locations = Location::active()->get();

        return view('admin.employees.index', [
            'employees' => $employees,
            'departments' => $departments,
            'locations' => $locations,
            'filters' => $request->only(['search', 'department_id', 'location_id', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        $this->checkAdminAccess();

        return view('admin.employees.create', [
            'departments' => Department::active()->get(),
            'designations' => Designation::active()->orderBy('level')->get(),
            'locations' => Location::active()->get(),
            'shifts' => Shift::active()->get(),
            'managers' => User::where('role', '!=', 'employee')->active()->get(),
        ]);
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'employee_id' => 'required|string|unique:users,employee_id',
            'phone' => 'nullable|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'location_id' => 'required|exists:locations,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'manager_id' => 'nullable|exists:users,id',
            'role' => 'required|in:employee,manager,hr_admin,super_admin',
            'joining_date' => 'required|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $validated['password'] = Hash::make('password'); // Default password
        $validated['is_active'] = true;

        $employee = User::create($validated);

        // Create default leave balances
        $this->createDefaultLeaveBalances($employee);

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee '{$employee->name}' created successfully. Default password is 'password'.");
    }

    /**
     * Display the specified employee
     */
    public function show(User $employee)
    {
        $this->checkAdminAccess();

        $employee->load(['department', 'designation', 'location', 'shift', 'manager', 'leaveBalances.leaveType']);

        return view('admin.employees.show', [
            'employee' => $employee,
        ]);
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit(User $employee)
    {
        $this->checkAdminAccess();

        return view('admin.employees.edit', [
            'employee' => $employee,
            'departments' => Department::active()->get(),
            'designations' => Designation::active()->orderBy('level')->get(),
            'locations' => Location::active()->get(),
            'shifts' => Shift::active()->get(),
            'managers' => User::where('role', '!=', 'employee')
                ->where('id', '!=', $employee->id)
                ->active()
                ->get(),
        ]);
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, User $employee)
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($employee->id)],
            'employee_id' => ['required', 'string', Rule::unique('users')->ignore($employee->id)],
            'phone' => 'nullable|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'location_id' => 'required|exists:locations,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'manager_id' => 'nullable|exists:users,id',
            'role' => 'required|in:employee,manager,hr_admin,super_admin',
            'joining_date' => 'required|date',
            'gender' => 'nullable|in:male,female,other',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $employee->update($validated);

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee '{$employee->name}' updated successfully.");
    }

    /**
     * Remove the specified employee (soft delete)
     */
    public function destroy(User $employee)
    {
        $this->checkAdminAccess();

        // Cannot delete yourself
        if (Auth::id() === $employee->id) {
            return back()->with('error', 'You cannot deactivate yourself.');
        }

        $employee->update(['is_active' => false]);

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee '{$employee->name}' deactivated successfully.");
    }

    /**
     * Reset employee password
     */
    public function resetPassword(User $employee)
    {
        $this->checkAdminAccess();

        $employee->update([
            'password' => Hash::make('password'),
        ]);

        return back()->with('success', "Password reset to 'password' for {$employee->name}.");
    }

    /**
     * Create default leave balances for new employee
     */
    private function createDefaultLeaveBalances(User $employee): void
    {
        $leaveTypes = \App\Models\LeaveType::active()->get();
        $year = now()->year;

        foreach ($leaveTypes as $leaveType) {
            \App\Models\LeaveBalance::create([
                'user_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year' => $year,
                'total_entitlement' => $leaveType->total_days_per_year,
                'used' => 0,
                'pending' => 0,
                'carried_forward' => 0,
            ]);
        }
    }

    /**
     * Show the bulk edit form.
     */
    public function bulkIndex(Request $request)
    {
        $this->checkAdminAccess();

        $query = User::where('role', '!=', 'super_admin')->where('is_active', true);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('name')->get();

        $departments = Department::active()->orderBy('name')->get(); // Active only
        $designations = Designation::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get();
        $shifts = Shift::where('is_active', true)->orderBy('name')->get();
        $managers = User::whereIn('role', ['manager', 'hr_admin', 'super_admin'])->active()->orderBy('name')->get();

        return view('admin.employees.bulk', compact('employees', 'departments', 'designations', 'locations', 'shifts', 'managers'));
    }

    /**
     * Process bulk update.
     */
    public function bulkUpdate(Request $request)
    {
        $this->checkAdminAccess();

        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'location_id' => 'nullable|exists:locations,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $updates = [];
        if ($request->filled('department_id'))
            $updates['department_id'] = $request->department_id;
        if ($request->filled('designation_id'))
            $updates['designation_id'] = $request->designation_id;
        if ($request->filled('location_id'))
            $updates['location_id'] = $request->location_id;
        if ($request->filled('shift_id'))
            $updates['shift_id'] = $request->shift_id;
        if ($request->filled('manager_id'))
            $updates['manager_id'] = $request->manager_id;

        if (empty($updates)) {
            return back()->with('error', 'No changes selected to apply.');
        }

        User::whereIn('id', $request->employee_ids)->update($updates);

        return redirect()->route('admin.employees.bulk')->with('success', 'Employees updated successfully.');
    }
}
