<x-app-layout>
    @section('title', 'Daily Attendance Report')

    <x-slot name="header">
        Daily Attendance Report (Detailed)
    </x-slot>

    <x-slot name="actions">
        <button class="btn btn-primary btn-sm gap-2" onclick="printReport()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print
        </button>
        <button class="btn btn-success btn-sm gap-2" onclick="exportToExcel()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export Excel
        </button>
    </x-slot>

    <!-- SheetJS Library for Excel Export -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>

    <!-- Filters -->
    <div class="card bg-base-100 shadow border border-base-300 mb-6 no-print">
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
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6 no-print">
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

    <!-- Report Content -->
    <div id="printable-report">
        <!-- Print Header -->
        <div class="print-header hidden mb-6">
            <div class="text-center">
                <img src="/images/logo.png" alt="Keystone Infra" style="height: 50px;"
                    onerror="this.style.display='none'">
                <h1 class="text-2xl font-bold">Daily Attendance Report (Detailed Report)</h1>
                <p class="text-lg">{{ $date->format('M d Y') }} To {{ $date->format('M d Y') }}</p>
            </div>
            <div class="flex justify-between mt-4 text-sm">
                <div><strong>Company:</strong> Keystone Infra</div>
                <div><strong>Printed On:</strong> {{ now()->format('M d Y H:i') }}</div>
            </div>
            <div class="mt-2 text-sm">
                <strong>Attendance Date:</strong> {{ $date->format('d-M-Y') }}
            </div>
        </div>

        <!-- Report Table -->
        <div class="card bg-base-100 shadow-lg border border-base-300">
            <div class="card-body p-4">
                <h3 class="card-title mb-4 no-print">
                    {{ $date->format('l, d F Y') }}
                    @if($date->isToday())
                        <span class="badge badge-primary">Today</span>
                    @endif
                </h3>

                <div class="overflow-x-auto">
                    <table class="table table-xs table-zebra w-full" id="report-table">
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

                                    $schedIn = $shift?->start_time ? \Carbon\Carbon::parse($shift->start_time)->format('H:i') : '09:30';
                                    $schedOut = $shift?->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : '18:30';

                                    $actualIn = $attendance?->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('H:i') : '00:00';
                                    $actualOut = $attendance?->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('H:i') : '00:00';

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

                                    $ot = '00:00';
                                    if ($totalHours > 8) {
                                        $otHours = $totalHours - 8;
                                        $ot = sprintf('%02d:%02d', floor($otHours), round(($otHours - floor($otHours)) * 60));
                                    }

                                    $lateBy = '00:00';
                                    if ($attendance && $attendance->punch_in_time && $shift) {
                                        $schedStart = \Carbon\Carbon::parse($shift->start_time);
                                        $actualStart = \Carbon\Carbon::parse($attendance->punch_in_time);
                                        if ($actualStart->gt($schedStart)) {
                                            $lateMins = $actualStart->diffInMinutes($schedStart);
                                            $lateBy = sprintf('%02d:%02d', floor($lateMins / 60), $lateMins % 60);
                                        }
                                    }

                                    $earlyBy = '00:00';
                                    if ($attendance && $attendance->punch_out_time && $shift) {
                                        $schedEnd = \Carbon\Carbon::parse($shift->end_time);
                                        $actualEnd = \Carbon\Carbon::parse($attendance->punch_out_time);
                                        if ($actualEnd->lt($schedEnd)) {
                                            $earlyMins = $schedEnd->diffInMinutes($actualEnd);
                                            $earlyBy = sprintf('%02d:%02d', floor($earlyMins / 60), $earlyMins % 60);
                                        }
                                    }

                                    $statusLabels = [
                                        'present' => 'Present',
                                        'late' => 'Late',
                                        'half_day' => 'Half Day',
                                        'absent' => 'Absent',
                                        'leave' => 'Leave',
                                        'week_off' => 'Week Off',
                                    ];
                                    $statusColors = [
                                        'present' => 'text-success',
                                        'late' => 'text-warning',
                                        'half_day' => 'text-info',
                                        'absent' => 'text-error',
                                        'leave' => 'text-secondary',
                                        'week_off' => 'text-neutral',
                                    ];
                                @endphp
                                <tr class="text-xs">
                                    <td>{{ $index + 1 }}</td>
                                    <td class="font-mono">{{ $employee->employee_id ?? '-' }}</td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $shift?->name ?? 'GS' }}</td>
                                    <td class="font-mono">{{ $schedIn }}</td>
                                    <td class="font-mono">{{ $schedOut }}</td>
                                    <td class="font-mono">{{ $actualIn }}</td>
                                    <td class="font-mono">{{ $actualOut }}</td>
                                    <td class="font-mono">{{ $workDur }}</td>
                                    <td class="font-mono">{{ $ot }}</td>
                                    <td class="font-mono font-semibold">{{ $workDur }}</td>
                                    <td class="font-mono">{{ $lateBy }}</td>
                                    <td class="font-mono">{{ $earlyBy }}</td>
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
    </div>

    <!-- Print Styles -->
    <style>
        @media print {

            /* Hide sidebar and non-printable elements */
            .drawer-side,
            .navbar,
            .no-print,
            footer,
            .drawer-toggle {
                display: none !important;
            }

            .drawer-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            /* Show print header */
            .print-header {
                display: block !important;
            }

            /* Table styles */
            body {
                font-size: 9px !important;
                -webkit-print-color-adjust: exact !important;
            }

            table {
                font-size: 8px !important;
                width: 100% !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
            }

            .card-body {
                padding: 10px !important;
            }

            /* Page setup */
            @page {
                size: landscape;
                margin: 10mm;
            }
        }

        .print-header {
            display: none;
        }
    </style>

    <script>
        function printReport() {
            window.print();
        }

        function exportToExcel() {
            // Get table data
            const table = document.getElementById('report-table');
            const wb = XLSX.utils.book_new();

            // Create header row with company info
            const headerData = [
                ['Daily Attendance Report (Detailed Report)'],
                ['Date: {{ $date->format("d-M-Y") }}'],
                ['Company: Keystone Infra', '', '', '', '', '', '', '', '', '', '', '', 'Printed: {{ now()->format("d-M-Y H:i") }}'],
                [] // Empty row
            ];

            // Get table data
            const ws = XLSX.utils.table_to_sheet(table);

            // Add header rows at the beginning
            XLSX.utils.sheet_add_aoa(ws, headerData, { origin: 'A1' });

            // Create new sheet with header + table
            const finalWs = XLSX.utils.table_to_sheet(table);

            // Add to workbook
            XLSX.utils.book_append_sheet(wb, finalWs, 'Attendance Report');

            // Generate filename
            const filename = 'Daily_Attendance_{{ $date->format("Y-m-d") }}.xlsx';

            // Download
            XLSX.writeFile(wb, filename);
        }
    </script>
</x-app-layout>