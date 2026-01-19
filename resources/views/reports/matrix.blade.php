<x-app-layout>
    @section('title', 'Monthly Attendance Matrix')

    <x-slot name="header">
        Monthly Attendance Matrix
    </x-slot>

    <x-slot name="actions">
        <div class="flex gap-2">
            <form method="GET" class="flex gap-2 items-end">
                <div class="form-control w-full max-w-xs">
                    <label class="label"><span class="label-text">Month</span></label>
                    <select name="month" class="select select-bordered select-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-control w-full max-w-xs">
                    <label class="label"><span class="label-text">Year</span></label>
                    <select name="year" class="select select-bordered select-sm">
                        @for($y = now()->year; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-control w-full max-w-xs">
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
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </form>

            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export CSV
            </a>
        </div>
    </x-slot>

    <div class="card bg-base-100 shadow-xl overflow-x-auto">
        <div class="card-body p-4">
            <table class="table table-xs table-pin-rows table-pin-cols">
                <thead>
                    <tr>
                        <th class="bg-base-200 z-20">Employee</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            <th class="text-center w-8 bg-base-200">{{ $d }}</th>
                        @endfor
                        <th class="bg-base-200 text-center">P</th>
                        <th class="bg-base-200 text-center">A</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matrix as $row)
                        <tr>
                            <td class="bg-base-100 font-bold sticky left-0 z-10">{{ $row->user->name }}</td>
                            @foreach($row->days as $day => $status)
                                @php
                                    $color = match ($status) {
                                        'P' => 'bg-success/20 text-success-content',
                                        'L' => 'bg-warning/20 text-warning-content',
                                        'HD' => 'bg-info/20 text-info-content',
                                        'A' => 'bg-error/20 text-error-content',
                                        'LV' => 'bg-secondary/20 text-secondary-content',
                                        'WO' => 'bg-base-200 text-base-content/50',
                                        default => ''
                                    };
                                @endphp
                                <td class="text-center font-mono border-l border-base-200 {{ $color }}">
                                    {{ $status }}
                                </td>
                            @endforeach
                            <td class="text-center font-bold text-success">{{ $row->present_count }}</td>
                            <td class="text-center font-bold text-error">{{ $row->absent_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $daysInMonth + 3 }}" class="text-center py-8 text-base-content/60">
                                No employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 flex gap-4 text-xs">
        <div class="flex items-center gap-1"><span class="w-3 h-3 bg-success/20"></span> P: Present</div>
        <div class="flex items-center gap-1"><span class="w-3 h-3 bg-warning/20"></span> L: Late</div>
        <div class="flex items-center gap-1"><span class="w-3 h-3 bg-info/20"></span> HD: Half Day</div>
        <div class="flex items-center gap-1"><span class="w-3 h-3 bg-error/20"></span> A: Absent</div>
        <div class="flex items-center gap-1"><span class="w-3 h-3 bg-secondary/20"></span> LV: Leave</div>
        <div class="flex items-center gap-1"><span class="w-3 h-3 bg-base-200"></span> WO: Week Off</div>
    </div>
</x-app-layout>