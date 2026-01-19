<x-app-layout>
    @section('title', 'Monthly Status Report')

    <x-slot name="header">
        Monthly Status Report (Detailed Work Duration)
    </x-slot>

    <style>
        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            body {
                background: white !important;
                font-size: 10px;
            }

            .drawer,
            .drawer-content {
                display: block !important;
                height: auto !important;
                overflow: visible !important;
            }

            .drawer-side,
            .navbar,
            footer,
            .print\:hidden {
                display: none !important;
            }

            main {
                padding: 0 !important;
                margin: 0 !important;
            }

            .bg-base-200 {
                background: white !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
            }

            .overflow-x-auto {
                overflow: visible !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse;
                font-size: 9px !important;
            }

            th,
            td {
                border-color: #000 !important;
                padding: 2px !important;
            }

            h1 {
                font-size: 16px !important;
            }
        }
    </style>

    <x-slot name="actions">
        <div class="flex flex-wrap gap-2 justify-end print:hidden">
            <form method="GET" class="flex flex-wrap gap-2 items-end">
                <div class="form-control w-24">
                    <label class="label text-xs py-1"><span class="label-text">Year</span></label>
                    <select name="year" class="select select-bordered select-xs">
                        @for($y = now()->year; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-control w-28">
                    <label class="label text-xs py-1"><span class="label-text">Month</span></label>
                    <select name="month" class="select select-bordered select-xs">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="form-control w-32">
                    <label class="label text-xs py-1"><span class="label-text">Department</span></label>
                    <select name="department_id" class="select select-bordered select-xs">
                        <option value="">All</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control w-32">
                    <label class="label text-xs py-1"><span class="label-text">Location</span></label>
                    <select name="location_id" class="select select-bordered select-xs">
                        <option value="">All</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ $locationId == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-xs">Filter</button>
            </form>

            <button onclick="window.print()" class="btn btn-secondary btn-xs gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>
        </div>
    </x-slot>

    <!-- Report Container -->
    <div class="bg-white p-4 min-w-[1200px] overflow-x-auto print:p-0 print:overflow-visible">

        <!-- Report Header -->
        <div class="text-center mb-6 border-b pb-4">
            <h1 class="text-xl font-bold uppercase text-gray-800">Monthly Status Report (Detailed Work Duration)</h1>
            <div class="text-sm font-medium text-gray-600 mt-1">
                {{ date('M d Y', mktime(0, 0, 0, $month, 1, $year)) }} To
                {{ date('M d Y', mktime(0, 0, 0, $month, $daysInMonth, $year)) }}
            </div>
            <div class="flex justify-between items-end mt-4 text-xs font-mono text-gray-500">
                <div>Company: KS</div>
                <div>Printed On: {{ now()->format('M d Y H:i') }}</div>
            </div>
        </div>

        @php
            // Helper to get day name initials for header
            $headerDays = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $ts = mktime(0, 0, 0, $month, $d, $year);
                $headerDays[$d] = [
                    'num' => $d,
                    'name' => date('D', $ts) // Mon, Tue...
                ];
            }
        @endphp

        <!-- Main Content -->
        @forelse($matrix as $deptName => $employees)
            <div class="mb-6 break-inside-avoid">
                <!-- Department Header -->
                <div class="font-bold text-gray-800 mb-2 border-b-2 border-gray-800 pb-1">
                    Department: {{ $deptName }}
                </div>

                @foreach($employees as $row)
                    <div class="mb-8 border border-gray-300 p-2 rounded-sm break-inside-avoid">
                        <!-- Employee Header -->
                        <div class="flex flex-col md:flex-row justify-between mb-2 text-xs">
                            <div class="font-bold text-gray-800 w-64">
                                Employee: {{ $row->user->employee_id }} : {{ $row->user->name }}
                            </div>
                            <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-1 text-[10px] text-gray-600">
                                <div>Total Work Duration: <span
                                        class="font-bold text-gray-900">{{ $row->formatted_summary['duration'] }} Hrs.</span>
                                </div>
                                <div>Total OT: <span class="font-bold text-gray-900">{{ $row->formatted_summary['ot'] }}
                                        Hrs.</span></div>
                                <div>Present: <span class="font-bold text-gray-900">{{ $row->summary->present }}</span></div>
                                <div>Absent: <span class="font-bold text-gray-900">{{ $row->summary->absent }}</span></div>
                                <div>WeeklyOff: <span class="font-bold text-gray-900">{{ $row->summary->weekly_off }}</span>
                                </div>
                                <div>Holidays: <span class="font-bold text-gray-900">{{ $row->summary->holidays }}</span></div>
                                <div>Leaves Taken: <span class="font-bold text-gray-900">{{ $row->summary->leaves }}</span>
                                </div>
                                <div>LateBy Hrs: <span
                                        class="font-bold text-gray-900">{{ $row->formatted_summary['late_hours'] }}</span></div>
                                <div>LateBy Days: <span class="font-bold text-gray-900">{{ $row->summary->late_days }}</span>
                                </div>
                                <div>EarlyBy Hrs: <span
                                        class="font-bold text-gray-900">{{ $row->formatted_summary['early_hours'] }}</span>
                                </div>
                                <div>EarlyGoing Days: <span
                                        class="font-bold text-gray-900">{{ $row->summary->early_days }}</span></div>
                                <div>Total Shift Count: <span
                                        class="font-bold text-gray-900">{{ $row->summary->shift_count }}</span></div>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-[10px] border border-gray-400">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border border-gray-400 px-1 py-0.5 w-20 text-left">Days</th>
                                        @foreach($headerDays as $hd)
                                            <th class="border border-gray-400 px-1 py-0.5 w-10 text-center font-normal">
                                                <div>{{ $hd['num'] }}</div>
                                                <div>{{ substr($hd['name'], 0, 2) }}</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Row: Status -->
                                    <tr>
                                        <th class="border border-gray-400 px-1 py-0.5 text-left bg-gray-50">Status</th>
                                        @for($d = 1; $d <= $daysInMonth; $d++)
                                                            <td
                                                                class="border border-gray-400 text-center font-bold
                                                                                                        {{ $row->days[$d]->status == 'P' ? 'text-green-700' :
                                            ($row->days[$d]->status == 'A' ? 'text-red-600' :
                                                ($row->days[$d]->status == 'WO' ? 'text-gray-400' : 'text-blue-600')) }}">
                                                                {{ $row->days[$d]->status }}
                                                            </td>
                                        @endfor
                                    </tr>
                                    <!-- Row: InTime -->
                                    <tr>
                                        <th class="border border-gray-400 px-1 py-0.5 text-left bg-gray-50">InTime</th>
                                        @for($d = 1; $d <= $daysInMonth; $d++)
                                            <td class="border border-gray-400 text-center">{{ $row->days[$d]->in_time }}</td>
                                        @endfor
                                    </tr>
                                    <!-- Row: OutTime -->
                                    <tr>
                                        <th class="border border-gray-400 px-1 py-0.5 text-left bg-gray-50">OutTime</th>
                                        @for($d = 1; $d <= $daysInMonth; $d++)
                                            <td class="border border-gray-400 text-center">{{ $row->days[$d]->out_time }}</td>
                                        @endfor
                                    </tr>
                                    <!-- Row: Duration -->
                                    <tr>
                                        <th class="border border-gray-400 px-1 py-0.5 text-left bg-gray-50">Duration</th>
                                        @for($d = 1; $d <= $daysInMonth; $d++)
                                            <td class="border border-gray-400 text-center">{{ $row->days[$d]->duration }}</td>
                                        @endfor
                                    </tr>
                                    <!-- Row: Late By -->
                                    <tr>
                                        <th class="border border-gray-400 px-1 py-0.5 text-left bg-gray-50">Late By</th>
                                        @for($d = 1; $d <= $daysInMonth; $d++)
                                            <td class="border border-gray-400 text-center text-red-500">
                                                {{ $row->days[$d]->late_by }}
                                            </td>
                                        @endfor
                                    </tr>
                                    <!-- Row: Early By -->
                                    <tr>
                                        <th class="border border-gray-400 px-1 py-0.5 text-left bg-gray-50">Early By</th>
                                        @for($d = 1; $d <= $daysInMonth; $d++)
                                            <td class="border border-gray-400 text-center text-red-500">
                                                {{ $row->days[$d]->early_by }}
                                            </td>
                                        @endfor
                                    </tr>
                                    <!-- Row: OT -->
                                    <tr>
                                        <th class="border border-gray-400 px-1 py-0.5 text-left bg-gray-50">OT</th>
                                        @for($d = 1; $d <= $daysInMonth; $d++)
                                            <td class="border border-gray-400 text-center">{{ $row->days[$d]->ot }}</td>
                                        @endfor
                                    </tr>
                                    <!-- Row: Shift -->
                                    <tr>
                                        <th class="border border-gray-400 px-1 py-0.5 text-left bg-gray-50">Shift</th>
                                        @for($d = 1; $d <= $daysInMonth; $d++)
                                            <td class="border border-gray-400 text-center text-gray-500">{{ $row->days[$d]->shift }}
                                            </td>
                                        @endfor
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="text-center py-12 text-gray-500">
                No data found for the selected criteria.
            </div>
        @endforelse
    </div>
</x-app-layout>