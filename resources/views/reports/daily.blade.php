<x-app-layout>
    @section('title', 'Daily Attendance')

    <x-slot name="header">
        Daily Attendance Report
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
            <form action="{{ route('reports.daily') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="form-control">
                    <label class="label"><span class="label-text">Date</span></label>
                    <input type="date" name="date" class="input input-bordered input-sm"
                        value="{{ $date->format('Y-m-d') }}">
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
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                <a href="{{ route('reports.daily', ['date' => now()->subDay()->format('Y-m-d')]) }}"
                    class="btn btn-ghost btn-sm">Yesterday</a>
                <a href="{{ route('reports.daily') }}" class="btn btn-ghost btn-sm">Today</a>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow border border-base-300 p-3">
            <div class="stat-title text-xs">Total</div>
            <div class="stat-value text-lg">{{ $summary['total'] }}</div>
        </div>
        <div class="stat bg-success/10 rounded-lg shadow border border-success/30 p-3">
            <div class="stat-title text-xs">Present</div>
            <div class="stat-value text-lg text-success">{{ $summary['present'] }}</div>
        </div>
        <div class="stat bg-warning/10 rounded-lg shadow border border-warning/30 p-3">
            <div class="stat-title text-xs">Late</div>
            <div class="stat-value text-lg text-warning">{{ $summary['late'] }}</div>
        </div>
        <div class="stat bg-info/10 rounded-lg shadow border border-info/30 p-3">
            <div class="stat-title text-xs">Half Day</div>
            <div class="stat-value text-lg text-info">{{ $summary['half_day'] }}</div>
        </div>
        <div class="stat bg-error/10 rounded-lg shadow border border-error/30 p-3">
            <div class="stat-title text-xs">Absent</div>
            <div class="stat-value text-lg text-error">{{ $summary['absent'] }}</div>
        </div>
        <div class="stat bg-secondary/10 rounded-lg shadow border border-secondary/30 p-3">
            <div class="stat-title text-xs">On Leave</div>
            <div class="stat-value text-lg text-secondary">{{ $summary['leave'] }}</div>
        </div>
        <div class="stat bg-neutral/10 rounded-lg shadow border border-neutral/30 p-3">
            <div class="stat-title text-xs">Week Off</div>
            <div class="stat-value text-lg">{{ $summary['week_off'] }}</div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <h3 class="card-title mb-4">
                {{ $date->format('l, d F Y') }}
                @if($date->isToday())
                    <span class="badge badge-primary">Today</span>
                @endif
            </h3>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200">
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Shift</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyData as $row)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-10">
                                                <span>{{ substr($row['employee']->name, 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $row['employee']->name }}</div>
                                            <div class="text-xs text-base-content/60">
                                                {{ $row['employee']->designation?->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $row['employee']->department?->name ?? 'N/A' }}</td>
                                <td>{{ $row['employee']->shift?->name ?? 'General' }}</td>
                                <td class="text-success font-mono">
                                    {{ $row['attendance']?->formatted_punch_in ?? '--:--' }}
                                </td>
                                <td class="text-error font-mono">
                                    {{ $row['attendance']?->formatted_punch_out ?? '--:--' }}
                                </td>
                                <td class="font-mono">
                                    {{ $row['attendance']?->total_hours ? number_format($row['attendance']->total_hours, 2) : '--' }}
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'present' => 'success',
                                            'late' => 'warning',
                                            'half_day' => 'info',
                                            'absent' => 'error',
                                            'leave' => 'secondary',
                                            'week_off' => 'neutral',
                                        ];
                                        $statusLabels = [
                                            'present' => 'Present',
                                            'late' => 'Late',
                                            'half_day' => 'Half Day',
                                            'absent' => 'Absent',
                                            'leave' => 'On Leave',
                                            'week_off' => 'Week Off',
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $statusColors[$row['status']] ?? 'neutral' }}">
                                        {{ $statusLabels[$row['status']] ?? $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-base-content/60 py-8">
                                    No employees found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>