<x-app-layout>
    @section('title', $employee->name)

    <x-slot name="header">
        Employee Details
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-primary btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
        </a>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="card bg-base-100 shadow-lg border border-base-300">
            <div class="card-body items-center text-center">
                <div class="avatar placeholder mb-4">
                    <div class="bg-primary text-primary-content rounded-full w-24">
                        <span class="text-3xl">{{ substr($employee->name, 0, 2) }}</span>
                    </div>
                </div>
                <h2 class="card-title">{{ $employee->name }}</h2>
                <p class="text-base-content/60">{{ $employee->designation?->name ?? 'No Designation' }}</p>
                <div class="badge badge-{{ $employee->role === 'super_admin' ? 'error' : 'primary' }}">
                    {{ ucfirst(str_replace('_', ' ', $employee->role)) }}
                </div>

                <div class="divider"></div>

                <div class="w-full text-left space-y-2">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/60" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm">{{ $employee->email }}</span>
                    </div>
                    @if($employee->phone)
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/60" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="text-sm">{{ $employee->phone }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/60" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                        <span class="text-sm font-mono">{{ $employee->employee_id }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Work Info -->
            <div class="card bg-base-100 shadow-lg border border-base-300">
                <div class="card-body">
                    <h3 class="card-title mb-4">Work Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-base-content/60">Department</span>
                            <p class="font-medium">{{ $employee->department?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-base-content/60">Location</span>
                            <p class="font-medium">{{ $employee->location?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-base-content/60">Shift</span>
                            <p class="font-medium">{{ $employee->shift?->name ?? 'General' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-base-content/60">Reporting Manager</span>
                            <p class="font-medium">{{ $employee->manager?->name ?? 'None' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-base-content/60">Joining Date</span>
                            <p class="font-medium">{{ $employee->joining_date?->format('d M Y') ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-base-content/60">Status</span>
                            <p>
                                @if($employee->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-error">Inactive</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Balances -->
            <div class="card bg-base-100 shadow-lg border border-base-300">
                <div class="card-body">
                    <h3 class="card-title mb-4">Leave Balances ({{ now()->year }})</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @forelse($employee->leaveBalances as $balance)
                            <div class="stat bg-base-200 rounded-lg p-4">
                                <div class="stat-title text-xs">{{ $balance->leaveType->code }}</div>
                                <div
                                    class="stat-value text-xl {{ $balance->available_balance > 0 ? 'text-success' : 'text-error' }}">
                                    {{ $balance->available_balance }}
                                </div>
                                <div class="stat-desc">of {{ $balance->total_entitlement }}</div>
                            </div>
                        @empty
                            <p class="col-span-4 text-base-content/60">No leave balances configured</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card bg-base-100 shadow-lg border border-base-300">
                <div class="card-body">
                    <h3 class="card-title mb-4">Actions</h3>
                    <div class="flex flex-wrap gap-2">
                        <form action="{{ route('admin.employees.reset-password', $employee) }}" method="POST"
                            class="inline">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-sm"
                                onclick="return confirm('Reset password to default?')">
                                Reset Password
                            </button>
                        </form>
                        <a href="{{ route('reports.attendance', ['employee_id' => $employee->id]) }}"
                            class="btn btn-outline btn-sm">
                            View Attendance
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>