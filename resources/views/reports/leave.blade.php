<x-app-layout>
    @section('title', 'Leave Report')

    <x-slot name="header">
        Leave Report
    </x-slot>

    <x-slot name="actions">
        <button class="btn btn-primary btn-sm gap-2" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print
        </button>
    </x-slot>

    <!-- Filters -->
    <div class="card bg-base-100 shadow border border-base-300 mb-6">
        <div class="card-body">
            <form action="{{ route('reports.leave') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="form-control">
                    <label class="label"><span class="label-text">Year</span></label>
                    <select name="year" class="select select-bordered select-sm">
                        @for($y = now()->year; $y >= now()->year - 3; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Location</span></label>
                    <select name="location_id" class="select select-bordered select-sm">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ $locationId == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Department</span></label>
                    <select name="department_id" class="select select-bordered select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ $departmentId == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                <a href="{{ route('reports.leave') }}" class="btn btn-ghost btn-sm">Reset</a>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Leave Type Summary -->
        <div class="card bg-base-100 shadow-lg border border-base-300">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">By Leave Type</h3>
                @forelse($leaveTypeSummary as $type => $summary)
                    <div class="flex justify-between items-center py-2 border-b border-base-200 last:border-0">
                        <span>{{ $type }}</span>
                        <div class="text-right">
                            <span class="badge badge-primary">{{ $summary['count'] }} requests</span>
                            <span class="text-sm text-base-content/60 ml-2">{{ $summary['days'] }} days</span>
                        </div>
                    </div>
                @empty
                    <p class="text-base-content/60 text-sm">No leave data for {{ $year }}</p>
                @endforelse
            </div>
        </div>

        <!-- Monthly Stats -->
        <div class="card bg-base-100 shadow-lg border border-base-300 lg:col-span-2">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">Monthly Distribution</h3>
                <div class="grid grid-cols-4 md:grid-cols-6 gap-2">
                    @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $month)
                        @php $days = $monthlyStats[$month] ?? 0; @endphp
                        <div class="stat bg-base-200 rounded-lg p-2 text-center">
                            <div class="text-xs text-base-content/60">{{ $month }}</div>
                            <div class="text-lg font-bold {{ $days > 0 ? 'text-primary' : 'text-base-content/30' }}">
                                {{ $days }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Leave List -->
    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <h3 class="card-title mb-4">Leave Applications - {{ $year }}</h3>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200">
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Days</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $leave->user->name }}</div>
                                    <div class="text-xs text-base-content/60">{{ $leave->user->employee_id }}</div>
                                </td>
                                <td>{{ $leave->user->department?->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-outline">{{ $leave->leaveType->code }}</span>
                                </td>
                                <td>{{ $leave->from_date->format('d M') }}</td>
                                <td>{{ $leave->to_date->format('d M') }}</td>
                                <td class="font-mono">{{ $leave->total_days }}</td>
                                <td>
                                    <span class="badge badge-{{ $leave->status_color }}">{{ $leave->status_label }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-base-content/60 py-8">
                                    No leave applications found for {{ $year }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>