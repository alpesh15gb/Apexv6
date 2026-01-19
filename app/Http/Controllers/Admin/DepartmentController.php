<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    private function checkAdminAccess(): void
    {
        if (!Auth::user()?->hasAdminAccess()) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function index()
    {
        $this->checkAdminAccess();

        $departments = Department::withCount('users')
            ->with('manager')
            ->orderBy('name')
            ->get();

        return view('admin.departments.index', [
            'departments' => $departments,
        ]);
    }

    public function create()
    {
        $this->checkAdminAccess();

        $managers = User::where('role', '!=', 'employee')->active()->get();

        return view('admin.departments.create', [
            'managers' => $managers,
        ]);
    }

    public function store(Request $request)
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:10|unique:departments,code',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', "Department '{$validated['name']}' created successfully.");
    }

    public function edit(Department $department)
    {
        $this->checkAdminAccess();

        $managers = User::where('role', '!=', 'employee')->active()->get();

        return view('admin.departments.edit', [
            'department' => $department,
            'managers' => $managers,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'code' => 'required|string|max:10|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', "Department '{$department->name}' updated successfully.");
    }

    public function destroy(Department $department)
    {
        $this->checkAdminAccess();

        // Check if department has employees
        if ($department->users()->count() > 0) {
            return back()->with('error', 'Cannot delete department with assigned employees.');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', "Department deleted successfully.");
    }
}
