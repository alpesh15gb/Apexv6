<x-app-layout>
    @section('title', 'Daily Attendance Report')

    <x-slot name="header">
        Daily Attendance Report (Detailed)
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
        <a href="{{ route('reports.daily', array_merge(request()->query(), ['export' => 'csv'])) }}"
            class="btn btn-outline btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export CSV
        </a>
    </x-slot>

    <!-- Print Header (visible only when printing) -->
    <div class="print-header hidden print:block mb-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-primary">Daily Attendance Report (Detailed Report)</h1>
            <p class="text-lg">{{ $date->format('M d Y') }} To {{ $date->format('M d Y') }}</p>
        </div>
        <div class="flex justify-between mt-4 text-sm">
            <div>
                <strong>Company:</strong> Keystone Infra
            </div>
            <div>
                <strong>Printed On:</strong> {{ now()->format('M d Y H:i') }}
            </div>
        </div>
        <div class="mt-2 text-sm">
            <strong>Attendance Date:</strong> {{ $date->format('d-M-Y') }}
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow border border-base-300 mb-6 print:hidden">
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
                <div class="form-control">
                    <label class="label"><span class="label-text">Department</span></label>
                    <select name="department_id" class="select select-bordered select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}" {{ ($departmentId ?? '') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
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
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6 print:hidden">
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
        <div class="card-body p-4">
            <h3 class="card-title mb-4 print:hidden">
                {{ $date->format('l, d F Y') }}
                @if($date->isToday())
                    <span class="badge badge-primary">Today</span>
                @endif
            </h3>

            <div class="overflow-x-auto">
                <table class="table table-xs table-zebra w-full">
                    <thead>
                        <tr class="bg-base-200 text-xs">
                            <th class="w-10">SNo</th>
                            <th>E.Code</th>
                            <th>Name</th>
                            <th>Shift</th>
                            <th>S.InTime</th>
                            <th>S.OutTime</th>
                            <th>A.InTime</th>
                            <th>A.OutTime</th>
                            <th>Work Dur.</th>
                            <th>OT</th>
                            <th>Tot.Dur.</th>
                            <th>LateBy</th>
                            <th>EarlyGoingBy</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyData as $index => $row)
                            @php
                                $employee = $row['employee'];
                                $attendance = $row['attendance'];
                                $shift = $employee->shift;

                                // Scheduled times from shift
                                $schedIn = $shift?->start_time ? \Carbon\Carbon::parse($shift->start_time)->format('H:i') : '09:30';
                                $schedOut = $shift?->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : '18:30';

                                // Actual times
                                $actualIn = $attendance?->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('H:i') : '00:00';
                                $actualOut = $attendance?->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('H:i') : '00:00';

                                // Work duration
                                $workDur = '00:00';
                                $totalHours = 0;
                                if ($attendance && $attendance->punch_in_time && $attendance->punch_out_time) {
                                    $inTime = \Carbon\Carbon::parse($attendance->punch_in_time);
                                    $outTime = \Carbon\Carbon::parse($attendance->punch_out_time);
                                    $totalHours = $outTime->diffInMinutes($inTime) / 60;
                                    $hours = floor($totalHours);
                                    $mins = round(($totalHours - $hours) * 60);
                                    $workDur = sprintf('%02d:%02d', $hours, $mins);
                                }

                                // OT calculation (after 8 hours)
                                $ot = '00:00';
                                if ($totalHours > 8) {
                                    $otHours = $totalHours - 8;
                                    $ot = sprintf('%02d:%02d', floor($otHours), round(($otHours - floor($otHours)) * 60));
                                }

                                // Late by calculation
                                $lateBy = '00:00';
                                if ($attendance && $attendance->punch_in_time && $shift) {
                                    $schedStart = \Carbon\Carbon::parse($shift->start_time);
                                    $actualStart = \Carbon\Carbon::parse($attendance->punch_in_time);
                                    if ($actualStart->gt($schedStart)) {
                                        $lateMins = $actualStart->diffInMinutes($schedStart);
                                        $lateBy = sprintf('%02d:%02d', floor($lateMins / 60), $lateMins % 60);
                                    }
                                }

                                // Early going by calculation
                                $earlyBy = '00:00';
                                if ($attendance && $attendance->punch_out_time && $shift) {
                                    $schedEnd = \Carbon\Carbon::parse($shift->end_time);
                                    $actualEnd = \Carbon\Carbon::parse($attendance->punch_out_time);
                                    if ($actualEnd->lt($schedEnd)) {
                                        $earlyMins = $schedEnd->diffInMinutes($actualEnd);
                                        $earlyBy = sprintf('%02d:%02d', floor($earlyMins / 60), $earlyMins % 60);
                                    }
                                }

                                // Status styling
                                $statusColors = [
                                    'present' => 'text-success',
                                    'late' => 'text-warning',
                                    'half_day' => 'text-info',
                                    'absent' => 'text-error',
                                    'leave' => 'text-secondary',
                                    'week_off' => 'text-neutral',
                                ];
                                $statusLabels = [
                                    'present' => 'Present',
                                    'late' => 'Late',
                                    'half_day' => 'Half Day',
                                    'absent' => 'Absent',
                                    'leave' => 'Leave',
                                    'week_off' => 'Week Off',
                                ];
                            @endphp
                            <tr class="text-xs">
                                <td>{{ $index + 1 }}</td>
                                <td class="font-mono">{{ $employee->employee_id ?? '-' }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $shift?->name ?? 'GS' }}</td>
                                <td class="font-mono">{{ $schedIn }}</td>
                                <td class="font-mono">{{ $schedOut }}</td>
                                <td class="font-mono {{ $actualIn != '00:00' ? 'text-success' : '' }}">{{ $actualIn }}</td>
                                <td class="font-mono {{ $actualOut != '00:00' ? 'text-error' : '' }}">{{ $actualOut }}</td>
                                <td class="font-mono">{{ $workDur }}</td>
                                <td class="font-mono">{{ $ot }}</td>
                                <td class="font-mono font-semibold">{{ $workDur }}</td>
                                <td class="font-mono text-warning">{{ $lateBy }}</td>
                                <td class="font-mono text-info">{{ $earlyBy }}</td>
                                <td class="{{ $statusColors[$row['status']] ?? '' }} font-semibold">
                                    {{ $statusLabels[$row['status']] ?? ucfirst($row['status']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center text-base-content/60 py-8">
                                    No employees found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            .print-header {
                display: block !important;
            }

            body {
                font-size: 10px;
            }

            table {
                font-size: 9px;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            .card-body {
                padding: 5px !important;
            }
        }
    </style>
</x-app-layout>