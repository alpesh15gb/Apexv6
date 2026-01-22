<x-app-layout>
    @section('title', 'Reports')

    <x-slot name="header">
        Reports Dashboard
    </x-slot>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow border border-base-300">
            <div class="stat-figure text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="stat-title">Total Employees</div>
            <div class="stat-value text-primary">{{ $stats['total_employees'] }}</div>
        </div>

        <div class="stat bg-base-100 rounded-lg shadow border border-base-300">
            <div class="stat-figure text-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-title">Present Today</div>
            <div class="stat-value text-success">{{ $stats['present_today'] }}</div>
        </div>

        <div class="stat bg-base-100 rounded-lg shadow border border-base-300">
            <div class="stat-figure text-info">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="stat-title">On Leave Today</div>
            <div class="stat-value text-info">{{ $stats['on_leave_today'] }}</div>
        </div>

        <div class="stat bg-base-100 rounded-lg shadow border border-base-300">
            <div class="stat-figure text-warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-title">Pending Approvals</div>
            <div class="stat-value text-warning">{{ $stats['pending_approvals'] }}</div>
        </div>
    </div>

    <!-- Report Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Daily Attendance Report -->
        <div class="card bg-base-100 shadow-lg border border-base-300 hover:shadow-xl transition-shadow">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-success/10 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-success" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title text-lg">Daily Attendance</h3>
                        <p class="text-sm text-base-content/60">View attendance for a specific date</p>
                    </div>
                </div>
                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('reports.daily') }}" class="btn btn-success btn-sm">View Report</a>
                </div>
            </div>
        </div>

        <!-- Monthly Attendance Report -->
        <div class="card bg-base-100 shadow-lg border border-base-300 hover:shadow-xl transition-shadow">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-primary/10 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title text-lg">Attendance Report</h3>
                        <p class="text-sm text-base-content/60">Employee attendance summary</p>
                    </div>
                </div>
                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('reports.attendance') }}" class="btn btn-primary btn-sm">View Report</a>
                </div>
            </div>
        </div>

        <!-- Leave Report -->
        <div class="card bg-base-100 shadow-lg border border-base-300 hover:shadow-xl transition-shadow">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-secondary/10 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-secondary" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title text-lg">Leave Report</h3>
                        <p class="text-sm text-base-content/60">Leave applications summary</p>
                    </div>
                </div>
                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('reports.leave') }}" class="btn btn-secondary btn-sm">View Report</a>
                </div>
            </div>
        </div>

        <!-- Matrix Report -->
        <div class="card bg-base-100 shadow-lg border border-base-300 hover:shadow-xl transition-shadow">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-accent/10 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-accent" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M3 14h18m-9-4v8m-7-6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8a2 2 0 012-2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title text-lg">Monthly Matrix</h3>
                        <p class="text-sm text-base-content/60">Full month view of all employees</p>
                    </div>
                </div>
                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('reports.matrix') }}" class="btn btn-accent btn-sm">View Report</a>
                </div>
            </div>
        </div>

        <!-- List of Logs Report -->
        <div class="card bg-base-100 shadow-lg border border-base-300 hover:shadow-xl transition-shadow">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-info/10 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-info" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title text-lg">List of Logs</h3>
                        <p class="text-sm text-base-content/60">Monthly attendance timesheet log</p>
                    </div>
                </div>
                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('reports.logs') }}" class="btn btn-info btn-sm">View Report</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>