<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Leave;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Reports dashboard
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->hasAdminAccess() && !$user->isManager()) {
            abort(403);
        }

        // Get summary stats
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'total_employees' => User::active()->count(),
            'present_today' => Attendance::whereDate('date', $today)
                ->whereIn('status', ['present', 'late'])
                ->count(),
            'on_leave_today' => Leave::approved()
                ->where('from_date', '<=', $today)
                ->where('to_date', '>=', $today)
                ->count(),
            'pending_approvals' => Leave::pending()->count(),
        ];

        return view('reports.index', [
            'stats' => $stats,
        ]);
    }

    /**
     * Attendance report
     */
    public function attendance(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasAdminAccess() && !$user->isManager()) {
            abort(403);
        }

        $fromDate = Carbon::parse($request->input('from_date', now()->startOfMonth()));
        $toDate = Carbon::parse($request->input('to_date', now()));
        $locationId = $request->input('location_id');
        $departmentId = $request->input('department_id');

        // Build employee query
        $employeesQuery = User::with(['department', 'location', 'designation'])
            ->active()
            ->where('role', 'employee');

        if ($locationId) {
            $employeesQuery->where('location_id', $locationId);
        }
        if ($departmentId) {
            $employeesQuery->where('department_id', $departmentId);
        }

        // For managers, only show subordinates
        if ($user->isManager() && !$user->hasAdminAccess()) {
            $employeesQuery->where('manager_id', $user->id);
        }

        $employees = $employeesQuery->get();
        $employeeIds = $employees->pluck('id');

        // Get attendance data
        $attendanceData = Attendance::whereIn('user_id', $employeeIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->groupBy('user_id');

        // Calculate working days
        $workingDays = $this->calculateWorkingDays($fromDate, $toDate);

        // Build report data
        $reportData = $employees->map(function ($employee) use ($attendanceData, $workingDays) {
            $attendance = $attendanceData->get($employee->id, collect());

            return [
                'employee' => $employee,
                'present' => $attendance->whereIn('status', ['present', 'late'])->count(),
                'late' => $attendance->where('status', 'late')->count(),
                'half_day' => $attendance->where('status', 'half_day')->count(),
                'absent' => $workingDays - $attendance->whereIn('status', ['present', 'late', 'half_day', 'leave'])->count(),
                'leave' => $attendance->where('status', 'leave')->count(),
                'total_hours' => round($attendance->sum('total_hours'), 2),
                'avg_hours' => $attendance->count() > 0
                    ? round($attendance->sum('total_hours') / $attendance->count(), 2)
                    : 0,
                'working_days' => $workingDays,
            ];
        });

        // Check for Export
        if ($request->has('export') && $request->export == 'csv') {
            return $this->exportAttendanceCSV($reportData, $fromDate, $toDate);
        }

        $locations = Location::active()->get();
        $departments = Department::active()->get();

        return view('reports.attendance', [
            'reportData' => $reportData,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'locationId' => $locationId,
            'departmentId' => $departmentId,
            'locations' => $locations,
            'departments' => $departments,
            'workingDays' => $workingDays,
        ]);
    }

    private function exportAttendanceCSV($reportData, $fromDate, $toDate)
    {
        $filename = "attendance_report_{$fromDate->format('Ymd')}_{$toDate->format('Ymd')}.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        return response()->stream(function () use ($reportData) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Employee ID', 'Name', 'Department', 'Location', 'Present', 'Late', 'Half Day', 'Absent', 'Leave', 'Total Hours', 'Avg Hours']);

            foreach ($reportData as $row) {
                fputcsv($handle, [
                    $row['employee']->employee_id,
                    $row['employee']->name,
                    $row['employee']->department->name ?? '-',
                    $row['employee']->location->name ?? '-',
                    $row['present'],
                    $row['late'],
                    $row['half_day'],
                    $row['absent'],
                    $row['leave'],
                    $row['total_hours'],
                    $row['avg_hours'],
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Leave report
     */
    public function leave(Request $request)
    {
        // ... (auth check same) ...
        $user = Auth::user();

        if (!$user->hasAdminAccess() && !$user->isManager()) {
            abort(403);
        }

        $year = (int) $request->input('year', now()->year);
        $locationId = $request->input('location_id');
        $departmentId = $request->input('department_id');

        // Build query
        $query = Leave::with(['user.department', 'user.location', 'leaveType'])
            ->whereYear('from_date', $year)
            ->whereIn('status', ['approved', 'pending']);

        if ($locationId) {
            $query->whereHas('user', fn($q) => $q->where('location_id', $locationId));
        }
        if ($departmentId) {
            $query->whereHas('user', fn($q) => $q->where('department_id', $departmentId));
        }

        if ($user->isManager() && !$user->hasAdminAccess()) {
            $subordinateIds = $user->subordinates->pluck('id');
            $query->whereIn('user_id', $subordinateIds);
        }

        $leaves = $query->orderBy('from_date', 'desc')->get();

        // Check Export
        if ($request->has('export') && $request->export == 'csv') {
            return $this->exportLeaveCSV($leaves);
        }

        // Summary by leave type
        $leaveTypeSummary = $leaves->where('status', 'approved')
            ->groupBy('leaveType.name')
            ->map(fn($group) => [
                'count' => $group->count(),
                'days' => $group->sum('total_days'),
            ]);

        // Summary by month
        $monthlyStats = $leaves->where('status', 'approved')
            ->groupBy(fn($l) => $l->from_date->format('M'))
            ->map(fn($group) => $group->sum('total_days'));

        $locations = Location::active()->get();
        $departments = Department::active()->get();

        return view('reports.leave', [
            'leaves' => $leaves,
            'leaveTypeSummary' => $leaveTypeSummary,
            'monthlyStats' => $monthlyStats,
            'year' => $year,
            'locationId' => $locationId,
            'departmentId' => $departmentId,
            'locations' => $locations,
            'departments' => $departments,
        ]);
    }

    private function exportLeaveCSV($leaves)
    {
        $filename = "leave_report_" . now()->format('Ymd') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        return response()->stream(function () use ($leaves) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Employee ID', 'Name', 'Type', 'From', 'To', 'Days', 'Status', 'Reason']);

            foreach ($leaves as $leave) {
                fputcsv($handle, [
                    $leave->user->employee_id,
                    $leave->user->name,
                    $leave->leaveType->name,
                    $leave->from_date->format('Y-m-d'),
                    $leave->to_date->format('Y-m-d'),
                    $leave->total_days,
                    ucfirst($leave->status),
                    $leave->reason,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Daily attendance report
     */
    public function daily(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasAdminAccess() && !$user->isManager()) {
            abort(403);
        }

        $date = Carbon::parse($request->input('date', now()));
        $locationId = $request->input('location_id');
        $departmentId = $request->input('department_id');

        // Get all active employees
        $employeesQuery = User::with(['department', 'designation', 'shift'])
            ->active()
            ->where('role', '!=', 'admin')
            ->where('role', '!=', 'super_admin');

        if ($locationId) {
            $employeesQuery->where('location_id', $locationId);
        }

        if ($departmentId) {
            $employeesQuery->where('department_id', $departmentId);
        }

        if ($user->isManager() && !$user->hasAdminAccess()) {
            $employeesQuery->where('manager_id', $user->id);
        }

        $employees = $employeesQuery->orderBy('employee_id')->get();

        // Get attendance for the date
        $attendanceMap = Attendance::whereIn('user_id', $employees->pluck('id'))
            ->whereDate('date', $date)
            ->get()
            ->keyBy('user_id');

        // Get leaves for the date
        $leavesMap = Leave::whereIn('user_id', $employees->pluck('id'))
            ->approved()
            ->where('from_date', '<=', $date)
            ->where('to_date', '>=', $date)
            ->get()
            ->keyBy('user_id');

        // Build daily data
        $dailyData = $employees->map(function ($employee) use ($attendanceMap, $leavesMap, $date) {
            $attendance = $attendanceMap->get($employee->id);
            $leave = $leavesMap->get($employee->id);

            $status = 'absent';
            if ($attendance) {
                $status = $attendance->status;
            } elseif ($leave) {
                $status = 'leave';
            } elseif ($date->isWeekend()) {
                $status = 'week_off';
            }

            return [
                'employee' => $employee,
                'attendance' => $attendance,
                'leave' => $leave,
                'status' => $status,
            ];
        });

        // Summary counts
        $summary = [
            'total' => $employees->count(),
            'present' => $dailyData->whereIn('status', ['present', 'late'])->count(),
            'late' => $dailyData->where('status', 'late')->count(),
            'half_day' => $dailyData->where('status', 'half_day')->count(),
            'absent' => $dailyData->where('status', 'absent')->count(),
            'leave' => $dailyData->where('status', 'leave')->count(),
            'week_off' => $dailyData->where('status', 'week_off')->count(),
        ];

        $locations = Location::active()->get();
        $departments = Department::active()->get();

        return view('reports.daily', [
            'dailyData' => $dailyData,
            'summary' => $summary,
            'date' => $date,
            'locationId' => $locationId,
            'departmentId' => $departmentId,
            'locations' => $locations,
            'departments' => $departments,
        ]);
    }

    /**
     * Matrix Report (Monthly)
     */
    public function matrix(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasAdminAccess() && !$user->isManager()) {
            abort(403);
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $locationId = $request->input('location_id');
        $departmentId = $request->input('department_id');

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Employees Query
        $employeesQuery = User::with(['department', 'location', 'designation'])
            ->active()
            ->where('role', 'employee');

        if ($locationId)
            $employeesQuery->where('location_id', $locationId);
        if ($departmentId)
            $employeesQuery->where('department_id', $departmentId);
        if ($user->isManager() && !$user->hasAdminAccess()) {
            $employeesQuery->where('manager_id', $user->id);
        }

        $employees = $employeesQuery->orderBy('name')->get();
        $employeeIds = $employees->pluck('id');

        // Attendance Data
        $attendances = Attendance::whereIn('user_id', $employeeIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');

        // Leaves Data
        $leaves = Leave::whereIn('user_id', $employeeIds)
            ->approved()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate]);
            })
            ->get();

        // Prepare Matrix Data
        $matrix = [];
        foreach ($employees as $employee) {
            $employeeAttendances = $attendances->get($employee->id, collect());
            $employeeLeaves = $leaves->where('user_id', $employee->id);

            $row = [
                'user' => $employee,
                'days' => []
            ];

            for ($day = 1; $day <= $startDate->daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day);
                $att = $employeeAttendances->where('date', $date)->first(); // Eloquent collection filter
                // Note: eloquent collection 'where' uses strict comparison by default for objects, 
                // but 'date' is cast to Carbon. Ideally compare strings or use first(cb).
                // Let's optimize: index by day
            }
        }

        // Optimization: Index by day inside the loop is slow.
        // Let's redo mapping

        $matrix = $employees->map(function ($employee) use ($attendances, $leaves, $startDate, $year, $month) {
            $empAtt = $attendances->get($employee->id, collect())->keyBy(fn($a) => $a->date->day);
            // Simple check if on leave
            // Leaves range check is complex per day. Simpler to rely on Attendance status if 'leave' punch exists? 
            // Or if we need strict Leave table check.
            // For Matrix, usually we show 'A' or 'P'.
            // Let's assume Attendance table is the source of truth for Status.

            $days = [];
            for ($d = 1; $d <= $startDate->daysInMonth; $d++) {
                $date = Carbon::create($year, $month, $d);
                $att = $empAtt->get($d);

                $status = $att ? ($att->status == 'half_day' ? 'HD' : ($att->status == 'present' ? 'P' : ($att->status == 'late' ? 'L' : ($att->status == 'leave' ? 'LV' : ($att->status == 'week_off' ? 'WO' : 'A'))))) : ($date->isWeekend() ? 'WO' : 'A');

                // If status is A but maybe Holiday? System holidays not yet implemented.

                $days[$d] = $status;
            }

            return (object) [
                'user' => $employee,
                'days' => $days,
                'present_count' => collect($days)->filter(fn($s) => in_array($s, ['P', 'L', 'HD']))->count(),
                'absent_count' => collect($days)->filter(fn($s) => $s === 'A')->count(),
            ];
        });

        // Export CSV
        if ($request->has('export') && $request->export == 'csv') {
            return $this->exportMatrixCSV($matrix, $startDate->daysInMonth, $month, $year);
        }

        $locations = Location::active()->get();
        $departments = Department::active()->get();

        return view('reports.matrix', [
            'matrix' => $matrix,
            'month' => $month,
            'year' => $year,
            'daysInMonth' => $startDate->daysInMonth,
            'locations' => $locations,
            'departments' => $departments,
            'locationId' => $locationId,
            'departmentId' => $departmentId,
        ]);
    }

    private function exportMatrixCSV($matrix, $daysInMonth, $month, $year)
    {
        $filename = "attendance_matrix_{$month}_{$year}.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        return response()->stream(function () use ($matrix, $daysInMonth) {
            $handle = fopen('php://output', 'w');

            // Header Row
            $header = ['Employee ID', 'Name', 'Department'];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $header[] = $d;
            }
            $header[] = 'Total Present';
            $header[] = 'Total Absent';
            fputcsv($handle, $header);

            // Data Rows
            foreach ($matrix as $row) {
                $data = [
                    $row->user->employee_id,
                    $row->user->name,
                    $row->user->department->name ?? '-',
                ];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $data[] = $row->days[$d];
                }
                $data[] = $row->present_count;
                $data[] = $row->absent_count;
                fputcsv($handle, $data);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Calculate working days
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
