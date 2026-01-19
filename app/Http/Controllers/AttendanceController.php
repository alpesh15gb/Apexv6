<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Punch in for the day
     */
    public function punchIn(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Check if already punched in
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($attendance && $attendance->punch_in_time) {
            return redirect()->route('dashboard')
                ->with('error', 'You have already punched in today.');
        }

        // Create or update attendance record
        $attendance = Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'punch_in_time' => now()->format('H:i:s'),
                'punch_in_ip' => $request->ip(),
                'status' => $this->calculateStatus($user, now()),
            ]
        );

        // Calculate late minutes
        if ($user->shift) {
            $lateMinutes = $user->shift->calculateLateMinutes(now(), $today);
            $attendance->update(['late_minutes' => $lateMinutes]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Punched in successfully at ' . now()->format('h:i A'));
    }

    /**
     * Punch out for the day
     */
    public function punchOut(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->punch_in_time) {
            return redirect()->route('dashboard')
                ->with('error', 'You need to punch in first.');
        }

        if ($attendance->punch_out_time) {
            return redirect()->route('dashboard')
                ->with('error', 'You have already punched out today.');
        }

        // Calculate total hours
        $punchIn = Carbon::parse($attendance->punch_in_time);
        $punchOut = now();
        $totalHours = round($punchIn->diffInMinutes($punchOut) / 60, 2);

        // Update attendance
        $attendance->update([
            'punch_out_time' => $punchOut->format('H:i:s'),
            'punch_out_ip' => $request->ip(),
            'total_hours' => $totalHours,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Punched out successfully at ' . now()->format('h:i A') . '. Total hours: ' . $totalHours);
    }

    /**
     * Calculate attendance status based on shift timings
     */
    private function calculateStatus($user, Carbon $punchInTime): string
    {
        if (!$user->shift) {
            return 'present';
        }

        $shift = $user->shift;
        $today = $punchInTime->copy()->startOfDay();

        if ($shift->isHalfDay($punchInTime, $today)) {
            return 'half_day';
        }

        if ($shift->isLate($punchInTime, $today)) {
            return 'late';
        }

        return 'present';
    }

    /**
     * Show attendance history
     */
    public function history(Request $request)
    {
        $user = Auth::user();

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();

        // Build calendar data indexed by day
        $calendarData = [];
        foreach ($attendances as $attendance) {
            $calendarData[$attendance->date->day] = $attendance;
        }

        // Calculate summary
        $summary = [
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'half_day' => $attendances->where('status', 'half_day')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
            'working_days' => $this->calculateWorkingDays($startDate, min(now(), $endDate)),
        ];

        return view('attendance.history', [
            'attendances' => $attendances->sortByDesc('date'),
            'calendarData' => $calendarData,
            'summary' => $summary,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Calculate working days between two dates (excluding weekends)
     */
    private function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        $days = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if (!$current->isWeekend()) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }
}
