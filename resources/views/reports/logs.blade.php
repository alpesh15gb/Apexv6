<x-app-layout>
    @section('title', 'List of Logs')

    <x-slot name="header">
        List of Logs
    </x-slot>

    <!-- Print Styles -->
    <style>
        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            .drawer-side,
            .drawer-toggle,
            .navbar,
            footer,
            aside,
            .menu,
            .print\:hidden,
            label[for="sidebar-drawer"] {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
                opacity: 0 !important;
                visibility: hidden !important;
            }

            html,
            body,
            .drawer,
            .drawer-content,
            main {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: none !important;
                background: white !important;
                height: auto !important;
                overflow: visible !important;
            }

            .bg-base-200,
            .bg-base-100 {
                background: white !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
            }

            .overflow-x-auto {
                overflow: visible !important;
            }

            body {
                font-size: 8px;
                color: black;
            }

            h1 {
                font-size: 14px !important;
                color: black !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse;
                font-size: 7px !important;
            }

            th,
            td {
                border-color: #000 !important;
                padding: 1px !important;
            }

            .break-inside-avoid {
                break-inside: avoid;
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

            <a href="{{ route('reports.logs', ['month' => $month, 'year' => $year, 'location_id' => $locationId, 'department_id' => $departmentId, 'export' => 'csv']) }}"
                class="btn btn-outline btn-xs gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                CSV
            </a>

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
    <div class="bg-white p-4 min-w-[1400px] overflow-x-auto print:p-0 print:overflow-visible print:min-w-0">

        <!-- Report Header -->
        <div class="text-center mb-4 border-b pb-3">
            <h1 class="text-xl font-bold uppercase text-gray-800">List of Logs</h1>
            <div class="text-sm font-medium text-gray-600 mt-1">
                {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
            </div>
            <div class="flex justify-between items-end mt-2 text-xs font-mono text-gray-500">
                <div>Date Range: {{ date('d/m/Y', mktime(0, 0, 0, $month, 1, $year)) }} To
                    {{ date('d/m/Y', mktime(0, 0, 0, $month, $daysInMonth, $year)) }}</div>
                <div>Printed On: {{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        @php
            $headerDays = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $ts = mktime(0, 0, 0, $month, $d, $year);
                $headerDays[$d] = [
                    'num' => $d,
                    'name' => date('D', $ts)
                ];
            }
        @endphp

        @if($logsData->count() > 0)
            <div class="overflow-x-auto print:overflow-visible">
                <table class="w-full border-collapse text-[9px] border border-gray-400">
                    <thead class="bg-gray-100">
                        <!-- Date Row -->
                        <tr>
                            <th rowspan="2" class="border border-gray-400 px-1 py-1 w-6 text-center bg-green-100">S.No</th>
                            <th rowspan="2" class="border border-gray-400 px-1 py-1 w-20 text-left bg-green-100">Emp ID</th>
                            <th rowspan="2" class="border border-gray-400 px-1 py-1 w-32 text-left bg-green-100">Name</th>
                            <th rowspan="2" class="border border-gray-400 px-1 py-1 w-24 text-left bg-green-100">Department
                            </th>
                            @foreach($headerDays as $hd)
                                <th
                                    class="border border-gray-400 px-0.5 py-0 text-center font-normal w-8 {{ in_array($hd['name'], ['Sat', 'Sun']) ? 'bg-gray-200' : '' }}">
                                    {{ $hd['num'] }}
                                </th>
                            @endforeach
                            <th rowspan="2" class="border border-gray-400 px-1 py-1 w-8 text-center bg-blue-100">P</th>
                            <th rowspan="2" class="border border-gray-400 px-1 py-1 w-8 text-center bg-red-100">A</th>
                            <th rowspan="2" class="border border-gray-400 px-1 py-1 w-8 text-center bg-gray-200">WO</th>
                            <th rowspan="2" class="border border-gray-400 px-1 py-1 w-8 text-center bg-yellow-100">L</th>
                            <th rowspan="2" class="border border-gray-400 px-1 py-1 w-12 text-center bg-green-100">Duration
                            </th>
                            <th rowspan="2" class="border border-gray-400 px-1 py-1 w-10 text-center bg-orange-100">OT</th>
                        </tr>
                        <!-- Day Name Row -->
                        <tr>
                            @foreach($headerDays as $hd)
                                <th
                                    class="border border-gray-400 px-0.5 py-0 text-center font-normal text-[8px] {{ in_array($hd['name'], ['Sat', 'Sun']) ? 'bg-gray-200' : '' }}">
                                    {{ substr($hd['name'], 0, 2) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logsData as $idx => $row)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-400 text-center font-medium">{{ $idx + 1 }}</td>
                                <td class="border border-gray-400 px-1 text-left font-medium">{{ $row->employee->employee_id }}
                                </td>
                                <td class="border border-gray-400 px-1 text-left truncate max-w-[120px]"
                                    title="{{ $row->employee->name }}">
                                    {{ Str::limit($row->employee->name, 18) }}
                                </td>
                                <td class="border border-gray-400 px-1 text-left text-[8px]">
                                    {{ $row->employee->department->name ?? '-' }}
                                </td>
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $day = $row->days[$d];
                                        $statusClass = match ($day->status) {
                                            'P' => 'text-green-700 font-bold',
                                            'A' => 'text-red-600 font-bold',
                                            'WO' => 'text-gray-400',
                                            'L' => 'text-blue-600 font-bold',
                                            'H' => 'text-purple-600',
                                            default => 'text-gray-600'
                                        };
                                        $bgClass = in_array($headerDays[$d]['name'], ['Sat', 'Sun']) ? 'bg-gray-50' : '';
                                    @endphp
                                    <td class="border border-gray-400 text-center {{ $statusClass }} {{ $bgClass }}"
                                        title="In: {{ $day->in_time }} | Out: {{ $day->out_time }} | Dur: {{ $day->duration }}">
                                        {{ $day->status }}
                                    </td>
                                @endfor
                                <td class="border border-gray-400 text-center font-bold text-green-700 bg-blue-50">
                                    {{ $row->summary->present }}</td>
                                <td class="border border-gray-400 text-center font-bold text-red-600 bg-red-50">
                                    {{ $row->summary->absent }}</td>
                                <td class="border border-gray-400 text-center text-gray-500 bg-gray-100">
                                    {{ $row->summary->weekly_off }}</td>
                                <td class="border border-gray-400 text-center text-blue-600 bg-yellow-50">
                                    {{ $row->summary->leave }}</td>
                                <td class="border border-gray-400 text-center font-bold bg-green-50">
                                    {{ $row->summary->total_duration }}</td>
                                <td class="border border-gray-400 text-center text-orange-600 bg-orange-50">
                                    {{ $row->summary->total_ot }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Legend -->
            <div class="mt-4 flex flex-wrap gap-4 text-xs print:mt-2">
                <div class="flex items-center gap-1">
                    <span class="font-bold text-green-700">P</span> = Present
                </div>
                <div class="flex items-center gap-1">
                    <span class="font-bold text-red-600">A</span> = Absent
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-gray-400">WO</span> = Weekly Off
                </div>
                <div class="flex items-center gap-1">
                    <span class="font-bold text-blue-600">L</span> = Leave
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-purple-600">H</span> = Holiday
                </div>
            </div>

            <!-- Summary -->
            <div class="mt-4 p-3 bg-gray-50 rounded text-xs print:mt-2">
                <div class="font-bold mb-2">Report Summary</div>
                <div class="flex flex-wrap gap-6">
                    <div>Total Employees: <span class="font-bold">{{ $logsData->count() }}</span></div>
                </div>
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                No data found for the selected criteria.
            </div>
        @endif
    </div>
</x-app-layout>