<x-app-layout>
    @section('title', 'Attendance Report')

    <x-slot name="header">
        Attendance Report
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
            <form action="{{ route('reports.attendance') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="form-control">
                    <label class="label"><span class="label-text">From Date</span></label>
                    <input type="date" name="from_date" class="input input-bordered input-sm"
                        value="{{ $fromDate->format('Y-m-d') }}">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">To Date</span></label>
                    <input type="date" name="to_date" class="input input-bordered input-sm"
                        value="{{ $toDate->format('Y-m-d') }}">
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
                <a href="{{ route('reports.attendance') }}" class="btn btn-ghost btn-sm">Reset</a>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="alert alert-info mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>
            Showing attendance from <strong>{{ $fromDate->format('d M Y') }}</strong> to
            <strong>{{ $toDate->format('d M Y') }}</strong>
            ({{ $workingDays }} working days) • <strong>{{ $reportData->count() }}</strong> employees
        </span>
    </div>

    <!-- Report Table -->
    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr class="bg-base-200">
                            <th>Employee</th>
                            <th>Department</th>
                            <th class="text-center">Present</th>
                            <th class="text-center">Late</th>
                            <th class="text-center">Half Day</th>
                            <th class="text-center">Absent</th>
                            <th class="text-center">Leave</th>
                            <th class="text-center">Total Hrs</th>
                            <th class="text-center">Avg Hrs</th>
                            <th class="text-center">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $row)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                <span class="text-xs">{{ substr($row['employee']->name, 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $row['employee']->name }}</div>
                                            <div class="text-xs text-base-content/60">{{ $row['employee']->employee_id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $row['employee']->department?->name ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-success badge-sm">{{ $row['present'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-warning badge-sm">{{ $row['late'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info badge-sm">{{ $row['half_day'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-error badge-sm">{{ $row['absent'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary badge-sm">{{ $row['leave'] }}</span>
                                </td>
                                <td class="text-center font-mono">{{ $row['total_hours'] }}</td>
                                <td class="text-center font-mono">{{ $row['avg_hours'] }}</td>
                                <td class="text-center">
                                    @php
                                        $percentage = $row['working_days'] > 0
                                            ? round(($row['present'] / $row['working_days']) * 100)
                                            : 0;
                                    @endphp
                                    <div class="radial-progress text-{{ $percentage >= 90 ? 'success' : ($percentage >= 75 ? 'warning' : 'error') }} text-xs"
                                        style="--value:{{ $percentage }}; --size:2.5rem;">
                                        {{ $percentage }}%
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-base-content/60 py-8">
                                    No data found for the selected filters
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>