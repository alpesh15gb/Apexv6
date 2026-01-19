<x-app-layout>
    @section('title', 'Attendance History')

    <x-slot name="header">
        My Attendance History
    </x-slot>

    <x-slot name="actions">
        <div class="flex gap-2">
            <select class="select select-bordered select-sm" id="month-select" onchange="updateFilters()">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>
            <select class="select select-bordered select-sm" id="year-select" onchange="updateFilters()">
                @for($y = now()->year; $y >= now()->year - 2; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button class="btn btn-primary btn-sm gap-2" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>
        </div>
    </x-slot>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow border border-base-300">
            <div class="stat-title text-xs">Present</div>
            <div class="stat-value text-success text-xl">{{ $summary['present'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow border border-base-300">
            <div class="stat-title text-xs">Late</div>
            <div class="stat-value text-warning text-xl">{{ $summary['late'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow border border-base-300">
            <div class="stat-title text-xs">Half Day</div>
            <div class="stat-value text-info text-xl">{{ $summary['half_day'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow border border-base-300">
            <div class="stat-title text-xs">Absent</div>
            <div class="stat-value text-error text-xl">{{ $summary['absent'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow border border-base-300">
            <div class="stat-title text-xs">Leave</div>
            <div class="stat-value text-secondary text-xl">{{ $summary['leave'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow border border-base-300">
            <div class="stat-title text-xs">Working Days</div>
            <div class="stat-value text-primary text-xl">{{ $summary['working_days'] }}</div>
        </div>
    </div>

    <!-- Calendar View -->
    <div class="card bg-base-100 shadow-lg border border-base-300 mb-6">
        <div class="card-body">
            <h3 class="card-title mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
            </h3>

            <!-- Calendar Grid -->
            <div class="grid grid-cols-7 gap-1">
                <!-- Day headers -->
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="text-center font-semibold text-sm p-2 text-base-content/60">{{ $day }}</div>
                @endforeach

                <!-- Empty cells for days before month starts -->
                @php
                    $firstDay = \Carbon\Carbon::create($year, $month, 1);
                    $daysInMonth = $firstDay->daysInMonth;
                    $startDayOfWeek = $firstDay->dayOfWeek;
                @endphp

                @for($i = 0; $i < $startDayOfWeek; $i++)
                    <div class="p-2"></div>
                @endfor

                <!-- Calendar days -->
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = \Carbon\Carbon::create($year, $month, $day);
                        $attendance = $calendarData[$day] ?? null;
                        $isToday = $date->isToday();
                        $isWeekend = $date->isWeekend();
                        $isFuture = $date->isFuture();

                        $bgClass = 'bg-base-200';
                        $textClass = '';

                        if ($isWeekend) {
                            $bgClass = 'bg-neutral/10';
                            $textClass = 'text-base-content/50';
                        }

                        if ($attendance) {
                            $bgClass = match ($attendance->status) {
                                'present' => 'bg-success/20 border-success',
                                'late' => 'bg-warning/20 border-warning',
                                'half_day' => 'bg-info/20 border-info',
                                'absent' => 'bg-error/20 border-error',
                                'leave' => 'bg-secondary/20 border-secondary',
                                'holiday' => 'bg-primary/20 border-primary',
                                'week_off' => 'bg-neutral/20 border-neutral',
                                default => 'bg-base-200',
                            };
                        }
                    @endphp

                    <div class="relative p-2 min-h-[80px] rounded-lg border {{ $bgClass }} {{ $isToday ? 'ring-2 ring-primary' : 'border-base-300' }} transition-all hover:shadow-md cursor-pointer"
                        onclick="showDayDetails({{ $day }})" x-data
                        x-tooltip="{{ $attendance ? $attendance->status_label : ($isWeekend ? 'Week Off' : ($isFuture ? 'Upcoming' : 'No Data')) }}">
                        <div class="font-semibold {{ $textClass }} {{ $isToday ? 'text-primary' : '' }}">
                            {{ $day }}
                        </div>

                        @if($attendance)
                            <div class="mt-1 text-xs space-y-0.5">
                                @if($attendance->punch_in_time)
                                    <div class="text-success">{{ $attendance->formatted_punch_in }}</div>
                                @endif
                                @if($attendance->punch_out_time)
                                    <div class="text-error">{{ $attendance->formatted_punch_out }}</div>
                                @endif
                            </div>
                            <div class="absolute bottom-1 right-1">
                                <span class="badge badge-xs badge-{{ $attendance->status_color }}">
                                    {{ substr($attendance->status_label, 0, 1) }}
                                </span>
                            </div>
                        @elseif($isWeekend)
                            <div class="text-xs text-base-content/40 mt-1">WO</div>
                        @endif
                    </div>
                @endfor
            </div>

            <!-- Legend -->
            <div class="flex flex-wrap gap-4 mt-6 pt-4 border-t border-base-300">
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-success/30 border border-success"></span>
                    <span class="text-sm">Present</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-warning/30 border border-warning"></span>
                    <span class="text-sm">Late</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-info/30 border border-info"></span>
                    <span class="text-sm">Half Day</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-error/30 border border-error"></span>
                    <span class="text-sm">Absent</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-secondary/30 border border-secondary"></span>
                    <span class="text-sm">Leave</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded bg-neutral/30 border border-neutral"></span>
                    <span class="text-sm">Week Off</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed List View -->
    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <h3 class="card-title mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Detailed Records
            </h3>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Total Hours</th>
                            <th>Late (mins)</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->date->format('d M Y') }}</td>
                                <td>{{ $attendance->date->format('l') }}</td>
                                <td class="text-success font-mono">{{ $attendance->formatted_punch_in ?? '--:--' }}</td>
                                <td class="text-error font-mono">{{ $attendance->formatted_punch_out ?? '--:--' }}</td>
                                <td class="font-mono">
                                    {{ $attendance->total_hours ? number_format($attendance->total_hours, 2) : '--' }}
                                </td>
                                <td>
                                    @if($attendance->late_minutes > 0)
                                        <span class="text-warning">{{ $attendance->late_minutes }}</span>
                                    @else
                                        <span class="text-success">0</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $attendance->status_color }}">
                                        {{ $attendance->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-xs btn-outline btn-primary"
                                        onclick="openRegularizeModal('{{ $attendance->date->format('Y-m-d') }}', '{{ $attendance->formatted_punch_in }}', '{{ $attendance->formatted_punch_out }}')">
                                        Regularize
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-base-content/60 py-8">
                                    No attendance records found for this month
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Regularization Modal -->
    <dialog id="regularize_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Request Regularization</h3>
            <form method="POST" action="{{ route('regularization.store') }}">
                @csrf
                <div class="form-control w-full mb-4">
                    <label class="label"><span class="label-text">Date</span></label>
                    <input type="date" name="date" id="reg_date" class="input input-bordered w-full" readonly />
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Punch In Time</span></label>
                        <input type="time" name="punch_in_time" id="reg_in" class="input input-bordered w-full"
                            required />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Punch Out Time</span></label>
                        <input type="time" name="punch_out_time" id="reg_out" class="input input-bordered w-full" />
                    </div>
                </div>

                <div class="form-control w-full mb-6">
                    <label class="label"><span class="label-text">Reason</span></label>
                    <textarea name="reason" class="textarea textarea-bordered h-24"
                        placeholder="Forgot punch / Device error / Work from home..." required></textarea>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn"
                        onclick="document.getElementById('regularize_modal').close()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <script>
        function updateFilters() {
            const month = document.getElementById('month-select').value;
            const year = document.getElementById('year-select').value;
            window.location.href = `{{ route('attendance.history') }}?month=${month}&year=${year}`;
        }

        function showDayDetails(day) {
            // Calculate date string YYYY-MM-DD
            const year = document.getElementById('year-select').value;
            const month = document.getElementById('month-select').value.toString().padStart(2, '0');
            const dayStr = day.toString().padStart(2, '0');
            const dateStr = `${year}-${month}-${dayStr}`;

            // Allow regularization for any past/today date
            const selectedDate = new Date(dateStr);
            const today = new Date();

            if (selectedDate <= today) {
                openRegularizeModal(dateStr, '', '');
            }
        }

        function openRegularizeModal(date, inTime, outTime) {
            document.getElementById('reg_date').value = date;

            // Helper to clean time string (e.g. "09:00 AM" -> "09:00")
            // Input type="time" expects HH:mm (24h)
            // If the inputs are already 24h formatted or need conversion
            // PHP formatted_punch_in likely returns H:i or h:i A. 
            // Let's assume user inputs fresh if blank. 
            // If we want to pre-fill, we need 24h format. 
            // For now, leave blank if complex to parse JS side, or just set if simple.

            document.getElementById('regularize_modal').showModal();
        }
    </script>
</x-app-layout>