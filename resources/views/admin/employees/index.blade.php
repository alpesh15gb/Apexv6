<x-app-layout>
    @section('title', 'Employees')

    <x-slot name="header">
        Employee Management
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.employees.bulk') }}" class="btn btn-secondary btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Bulk Edit
        </a>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Employee
        </a>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filters -->
    <div class="card bg-base-100 shadow border border-base-300 mb-6">
        <div class="card-body py-4">
            <form action="{{ route('admin.employees.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="form-control flex-1 min-w-[200px]">
                    <label class="label"><span class="label-text">Search</span></label>
                    <input type="text" name="search" class="input input-bordered input-sm"
                        placeholder="Name, email, or employee ID..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Department</span></label>
                    <select name="department_id" class="select select-bordered select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Location</span></label>
                    <select name="location_id" class="select select-bordered select-sm">
                        <option value="">All Locations</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ ($filters['location_id'] ?? '') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Status</span></label>
                    <select name="status" class="select select-bordered select-sm">
                        <option value="">All</option>
                        <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active
                        </option>
                        <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Inactive
                        </option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            </form>
        </div>
    </div>

    <!-- Employee List -->
    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="card-title">
                    Employees ({{ $employees->total() }})
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200">
                            <th>Employee</th>
                            <th>ID</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-10">
                                                <span>{{ substr($employee->name, 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $employee->name }}</div>
                                            <div class="text-sm text-base-content/60">{{ $employee->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-mono">{{ $employee->employee_id }}</td>
                                <td>{{ $employee->department?->name ?? 'N/A' }}</td>
                                <td>{{ $employee->location?->name ?? 'N/A' }}</td>
                                <td>
                                    <span
                                        class="badge badge-{{ $employee->role === 'super_admin' ? 'error' : ($employee->role === 'hr_admin' ? 'warning' : ($employee->role === 'manager' ? 'info' : 'ghost')) }} badge-sm">
                                        {{ ucfirst(str_replace('_', ' ', $employee->role)) }}
                                    </span>
                                </td>
                                <td>{{ $employee->joining_date?->format('d M Y') ?? 'N/A' }}</td>
                                <td>
                                    @if($employee->is_active)
                                        <span class="badge badge-success badge-sm">Active</span>
                                    @else
                                        <span class="badge badge-error badge-sm">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="btn btn-ghost btn-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </label>
                                        <ul tabindex="0"
                                            class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40 border border-base-300">
                                            <li><a href="{{ route('admin.employees.show', $employee) }}">View</a></li>
                                            <li><a href="{{ route('admin.employees.edit', $employee) }}">Edit</a></li>
                                            <li>
                                                <form action="{{ route('admin.employees.reset-password', $employee) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        onclick="return confirm('Reset password to default?')">
                                                        Reset Password
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.employees.destroy', $employee) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-error"
                                                        onclick="return confirm('Deactivate this employee?')">
                                                        Deactivate
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-base-content/60 py-8">
                                    No employees found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
</x-app-layout>