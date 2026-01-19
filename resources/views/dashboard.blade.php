<x-app-layout>
    @section('title', 'Dashboard')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Management Overview') }}
        </h2>
    </x-slot>

    <!-- Admin Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Employees -->
        <div class="card bg-base-100 shadow-xl border border-base-200 cursor-pointer hover:shadow-2xl transition-shadow"
            onclick="showEmployees('total', 'Total Employees')">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm font-medium">Total Employees</p>
                        <h2 class="text-3xl font-bold mt-1">{{ $totalEmployees }}</h2>
                    </div>
                    <div class="p-3 bg-primary/10 rounded-xl text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-xs text-base-content/60 mt-4 flex items-center gap-1">
                    <span class="text-success font-bold">Click</span> to view all
                </div>
            </div>
        </div>

        <!-- Present Today -->
        <div class="card bg-base-100 shadow-xl border border-base-200 cursor-pointer hover:shadow-2xl transition-shadow"
            onclick="showEmployees('present', 'Present Today')">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm font-medium">Present Today</p>
                        <h2 class="text-3xl font-bold mt-1 text-success">{{ $presentToday }}</h2>
                    </div>
                    <div class="p-3 bg-success/10 rounded-xl text-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-xs text-base-content/60 mt-4">
                    <span class="text-success font-bold">Click</span> to view list
                </div>
            </div>
        </div>

        <!-- Late Today -->
        <div class="card bg-base-100 shadow-xl border border-base-200 cursor-pointer hover:shadow-2xl transition-shadow"
            onclick="showEmployees('late', 'Late Today')">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm font-medium">Late Today</p>
                        <h2 class="text-3xl font-bold mt-1 text-warning">{{ $lateToday }}</h2>
                    </div>
                    <div class="p-3 bg-warning/10 rounded-xl text-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-xs text-base-content/60 mt-4">
                    <span class="text-warning font-bold">Click</span> to view list
                </div>
            </div>
        </div>

        <!-- On Leave -->
        <div class="card bg-base-100 shadow-xl border border-base-200 cursor-pointer hover:shadow-2xl transition-shadow"
            onclick="showEmployees('leave', 'On Leave Today')">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm font-medium">On Leave</p>
                        <h2 class="text-3xl font-bold mt-1 text-info">{{ $onLeaveToday }}</h2>
                    </div>
                    <div class="p-3 bg-info/10 rounded-xl text-info">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="text-xs text-base-content/60 mt-4">
                    <span class="text-info font-bold">Click</span> to view list
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity Feed -->
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-xl border border-base-200">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="card-title text-base font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Recent Punch Activity
                        </h3>
                        <a href="{{ route('reports.attendance') }}" class="btn btn-ghost btn-xs">View All</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr class="bg-base-200/50">
                                    <th>Employee</th>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivity as $activity)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="avatar placeholder">
                                                    <div class="bg-neutral-focus text-neutral-content rounded-full w-8">
                                                        <span
                                                            class="text-xs">{{ substr($activity->user->name, 0, 2) }}</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-sm">{{ $activity->user->name }}</div>
                                                    <div class="text-xs opacity-50">
                                                        {{ $activity->user->department->name ?? 'No Dept' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="font-mono text-sm">
                                            @if($activity->punch_out_time)
                                                {{ Carbon\Carbon::parse($activity->punch_out_time)->format('h:i A') }}
                                            @elseif($activity->punch_in_time)
                                                {{ Carbon\Carbon::parse($activity->punch_in_time)->format('h:i A') }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($activity->punch_out_time)
                                                <span class="badge badge-ghost badge-sm gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-error"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M17 16l4-4m0 0l-4-4m4 4H7" />
                                                    </svg>
                                                    Out
                                                </span>
                                            @else
                                                <span class="badge badge-ghost badge-sm gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-success"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                                                    </svg>
                                                    In
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $activity->status_color }} badge-xs">{{ ucfirst($activity->status_label) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-8 text-base-content/60">No activity today</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Holidays & Quick Links -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="card bg-base-100 shadow-xl border border-base-200">
                <div class="card-body">
                    <h3 class="card-title text-base font-bold mb-4">Management Actions</h3>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('reports.daily') }}"
                            class="btn btn-primary btn-outline btn-sm justify-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Daily Report
                        </a>
                        <a href="{{ route('admin.employees.create') }}"
                            class="btn btn-outline btn-sm justify-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Add New Employee
                        </a>
                        <a href="{{ route('leave.approvals') }}" class="btn btn-outline btn-sm justify-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Pending Approvals
                        </a>
                    </div>
                </div>
            </div>

            <!-- Holidays -->
            <div class="card bg-base-100 shadow-xl border border-base-200">
                <div class="card-body">
                    <h3 class="card-title text-base font-bold mb-4">Upcoming Holidays</h3>
                    @forelse($upcomingHolidays as $holiday)
                        <div class="flex items-center gap-3 py-2 border-b border-base-200 last:border-0">
                            <div class="bg-primary/10 text-primary rounded-lg p-2 text-center min-w-[50px]">
                                <div class="text-lg font-bold">{{ $holiday->date->format('d') }}</div>
                                <div class="text-xs">{{ $holiday->date->format('M') }}</div>
                            </div>
                            <div>
                                <div class="font-medium text-sm">{{ $holiday->name }}</div>
                                <div class="text-xs text-base-content/60">{{ $holiday->date->format('l') }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-base-content/60 text-sm">No upcoming holidays</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Employee List Modal -->
    <dialog id="employee_modal" class="modal">
        <div class="modal-box max-w-3xl">
            <h3 class="font-bold text-lg mb-4" id="modal_title">Employees</h3>
            <div id="employee_loading" class="flex justify-center py-8">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
            <div id="employee_list" class="hidden">
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Punch In</th>
                                <th>Punch Out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="employee_tbody"></tbody>
                    </table>
                </div>
                <div id="employee_empty" class="hidden text-center py-8 text-base-content/60">
                    No employees found.
                </div>
            </div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Close</button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <script>
        function showEmployees(status, title) {
            const modal = document.getElementById('employee_modal');
            const loading = document.getElementById('employee_loading');
            const list = document.getElementById('employee_list');
            const tbody = document.getElementById('employee_tbody');
            const empty = document.getElementById('employee_empty');

            // Set Title
            document.getElementById('modal_title').textContent = title;

            // Show loading
            loading.classList.remove('hidden');
            list.classList.add('hidden');
            empty.classList.add('hidden');
            tbody.innerHTML = '';

            modal.showModal();

            // Fetch data
            fetch(`/dashboard/employees/${status}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    loading.classList.add('hidden');
                    list.classList.remove('hidden');

                    if (data.employees.length === 0) {
                        empty.classList.remove('hidden');
                        return;
                    }

                    data.employees.forEach(emp => {
                        const statusBadge = {
                            'present': 'badge-success',
                            'late': 'badge-warning',
                            'half_day': 'badge-info',
                            'absent': 'badge-error',
                            'leave': 'badge-secondary'
                        }[emp.status] || 'badge-ghost';

                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td class="font-mono">${emp.employee_id || '-'}</td>
                        <td class="font-semibold">${emp.name}</td>
                        <td class="text-success font-mono">${emp.punch_in}</td>
                        <td class="text-error font-mono">${emp.punch_out}</td>
                        <td><span class="badge ${statusBadge}">${emp.status}</span></td>
                    `;
                        tbody.appendChild(row);
                    });
                })
                .catch(err => {
                    loading.classList.add('hidden');
                    list.classList.remove('hidden');
                    empty.textContent = 'Error loading employees.';
                    empty.classList.remove('hidden');
                });
        }
    </script>
</x-app-layout>