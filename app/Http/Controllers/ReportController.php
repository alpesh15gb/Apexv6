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
        $employeesQuery = User::with(['department', 'location', 'designation', 'shift'])
            ->active()
            ->where('role', 'employee');

        if ($locationId)
            $employeesQuery->where('location_id', $locationId);
        if ($departmentId)
            $employeesQuery->where('department_id', $departmentId);
        if ($user->isManager() && !$user->hasAdminAccess()) {
            $employeesQuery->where('manager_id', $user->id);
        }

        $employees = $employeesQuery->orderBy('department_id')->orderBy('name')->get();
        $employeeIds = $employees->pluck('id');

        // Attendance Data
        $attendances = Attendance::whereIn('user_id', $employeeIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');

        // Group by Department
        $groupedMatrix = $employees->groupBy(fn($emp) => $emp->department->name ?? 'No Department');

        $matrix = $groupedMatrix->map(function ($departmentEmployees) use ($attendances, $startDate, $year, $month) {
            return $departmentEmployees->map(function ($employee) use ($attendances, $startDate, $year, $month) {
                $empAtt = $attendances->get($employee->id, collect())->keyBy(fn($a) => $a->date->day);

                $days = [];
                $summary = [
                    'duration' => 0, // minutes
                    'ot_hours' => 0,
                    'present' => 0,
                    'absent' => 0,
                    'weekly_off' => 0,
                    'holidays' => 0,
                    'leaves' => 0,
                    'late_hours' => 0, // minutes
                    'late_days' => 0,
                    'early_hours' => 0, // minutes
                    'early_days' => 0,
                    'shift_count' => 0
                ];

                for ($d = 1; $d <= $startDate->daysInMonth; $d++) {
                    $date = Carbon::create($year, $month, $d);
                    $att = $empAtt->get($d);

                    // Default Status
                    $status = 'A'; // Absent by default
                    if ($date->isWeekend()) {
                        // Check if attendance exists even on weekend
                        $status = $att ? ($att->status == 'present' || $att->status == 'late' ? 'P' : 'WO') : 'WO';
                    }

                    $data = [
                        'status' => $status,
                        'in_time' => '',
                        'out_time' => '',
                        'duration' => '',
                        'late_by' => '',
                        'early_by' => '',
                        'ot' => '',
                        'shift' => $employee->shift->name ?? 'GS'
                    ];

                    if ($att) {
                        // Map status to code
                        $sCode = 'A';
                        switch ($att->status) {
                            case 'present':
                                $sCode = 'P';
                                break;
                            case 'late':
                                $sCode = 'P';
                                break; // Treat late as Present in P count usually, or 'L'
                            case 'half_day':
                                $sCode = 'P';
                                break; // Or HD
                            case 'absent':
                                $sCode = 'A';
                                break;
                            case 'leave':
                                $sCode = 'L';
                                break;
                            case 'week_off':
                                $sCode = 'WO';
                                break;
                            case 'holiday':
                                $sCode = 'H';
                                break;
                            default:
                                $sCode = 'A';
                        }
                        $data['status'] = $sCode;

                        $data['in_time'] = $att->punch_in_time ? Carbon::parse($att->punch_in_time)->format('H:i') : '';
                        $data['out_time'] = $att->punch_out_time ? Carbon::parse($att->punch_out_time)->format('H:i') : '';

                        // Formats
                        $data['duration'] = $this->minutesToTime($att->total_hours * 60);
                        $data['late_by'] = $att->late_minutes > 0 ? $this->minutesToTime($att->late_minutes) : '';
                        $data['early_by'] = $att->early_departure_minutes > 0 ? $this->minutesToTime($att->early_departure_minutes) : '';
                        $data['ot'] = $att->overtime_hours > 0 ? $this->minutesToTime($att->overtime_hours * 60) : '';

                        // Accumulate Summary
                        if (in_array($sCode, ['P', 'L', 'HD']))
                            $summary['present']++;
                        if ($sCode == 'A')
                            $summary['absent']++;
                        if ($sCode == 'WO')
                            $summary['weekly_off']++;
                        if ($sCode == 'L')
                            $summary['leaves']++;

                        $summary['duration'] += ($att->total_hours * 60);
                        $summary['ot_hours'] += $att->overtime_hours;

                        if ($att->late_minutes > 0) {
                            $summary['late_hours'] += $att->late_minutes;
                            $summary['late_days']++;
                        }
                        if ($att->early_departure_minutes > 0) {
                            $summary['early_hours'] += $att->early_departure_minutes;
                            $summary['early_days']++;
                        }
                        $summary['shift_count']++; // Assuming present = shift count? Or calculated from shift days.
                    } else {
                        if ($status == 'WO')
                            $summary['weekly_off']++;
                        else
                            $summary['absent']++; // If not weekend and no attendance -> absent
                    }

                    $days[$d] = (object) $data;
                }

                return (object) [
                    'user' => $employee,
                    'days' => $days,
                    'summary' => (object) $summary,
                    'formatted_summary' => [
                        'duration' => $this->minutesToTime($summary['duration']),
                        'ot' => $this->minutesToTime($summary['ot_hours'] * 60),
                        'late_hours' => $this->minutesToTime($summary['late_hours']),
                        'early_hours' => $this->minutesToTime($summary['early_hours']),
                    ]
                ];
            });
        });

        // Export CSV logic here (if needed, skipping for now to focus on View)

        $locations = Location::active()->get();
        $departments = Department::active()->get();

        return view('reports.matrix', [
            'matrix' => $matrix, // Now grouped by Dept
            'month' => $month,
            'year' => $year,
            'daysInMonth' => $startDate->daysInMonth,
            'locations' => $locations,
            'departments' => $departments,
            'locationId' => $locationId,
            'departmentId' => $departmentId,
        ]);
    }

    /**
     * List of Logs Report (Monthly timesheet format)
     */
    public function logs(Request $request)
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
        $employeesQuery = User::with(['department', 'location', 'designation', 'shift'])
            ->active()
            ->where('role', 'employee');

        if ($locationId)
            $employeesQuery->where('location_id', $locationId);
        if ($departmentId)
            $employeesQuery->where('department_id', $departmentId);
        if ($user->isManager() && !$user->hasAdminAccess()) {
            $employeesQuery->where('manager_id', $user->id);
        }

        $employees = $employeesQuery->orderBy('department_id')->orderBy('name')->get();
        $employeeIds = $employees->pluck('id');

        // Attendance Data
        $attendances = Attendance::whereIn('user_id', $employeeIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');

        // Build logs data
        $logsData = $employees->map(function ($employee) use ($attendances, $startDate, $year, $month) {
            $empAtt = $attendances->get($employee->id, collect())->keyBy(fn($a) => $a->date->day);

            $days = [];
            $totalDuration = 0;
            $totalOT = 0;
            $presentCount = 0;
            $absentCount = 0;
            $weeklyOffCount = 0;
            $leaveCount = 0;
            $lateDays = 0;
            $earlyDays = 0;

            for ($d = 1; $d <= $startDate->daysInMonth; $d++) {
                $date = Carbon::create($year, $month, $d);
                $att = $empAtt->get($d);

                $dayData = [
                    'date' => $date->format('d'),
                    'day' => $date->format('D'),
                    'status' => 'A',
                    'in_time' => '-',
                    'out_time' => '-',
                    'duration' => '-',
                    'ot' => '-',
                    'late' => '',
                    'early' => '',
                ];

                if ($date->isWeekend()) {
                    $dayData['status'] = $att ? ($att->status == 'present' || $att->status == 'late' ? 'P' : 'WO') : 'WO';
                    if ($dayData['status'] == 'WO') {
                        $weeklyOffCount++;
                    }
                }

                if ($att) {
                    switch ($att->status) {
                        case 'present':
                        case 'late':
                        case 'half_day':
                            $dayData['status'] = 'P';
                            $presentCount++;
                            break;
                        case 'absent':
                            $dayData['status'] = 'A';
                            $absentCount++;
                            break;
                        case 'leave':
                            $dayData['status'] = 'L';
                            $leaveCount++;
                            break;
                        case 'week_off':
                            $dayData['status'] = 'WO';
                            break;
                        case 'holiday':
                            $dayData['status'] = 'H';
                            break;
                        default:
                            $dayData['status'] = 'A';
                            $absentCount++;
                    }

                    $dayData['in_time'] = $att->punch_in_time ? Carbon::parse($att->punch_in_time)->format('H:i') : '-';
                    $dayData['out_time'] = $att->punch_out_time ? Carbon::parse($att->punch_out_time)->format('H:i') : '-';
                    $dayData['duration'] = $att->total_hours > 0 ? sprintf('%02d:%02d', floor($att->total_hours), ($att->total_hours - floor($att->total_hours)) * 60) : '-';
                    $dayData['ot'] = $att->overtime_hours > 0 ? sprintf('%02d:%02d', floor($att->overtime_hours), ($att->overtime_hours - floor($att->overtime_hours)) * 60) : '-';
                    $dayData['late'] = $att->late_minutes > 0 ? $this->minutesToTime($att->late_minutes) : '';
                    $dayData['early'] = $att->early_departure_minutes > 0 ? $this->minutesToTime($att->early_departure_minutes) : '';

                    $totalDuration += $att->total_hours * 60;
                    $totalOT += $att->overtime_hours * 60;

                    if ($att->late_minutes > 0)
                        $lateDays++;
                    if ($att->early_departure_minutes > 0)
                        $earlyDays++;
                } else {
                    if (!$date->isWeekend()) {
                        $absentCount++;
                    }
                }

                $days[$d] = (object) $dayData;
            }

            return (object) [
                'employee' => $employee,
                'days' => $days,
                'summary' => (object) [
                    'total_duration' => $this->minutesToTime($totalDuration),
                    'total_ot' => $this->minutesToTime($totalOT),
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'weekly_off' => $weeklyOffCount,
                    'leave' => $leaveCount,
                    'late_days' => $lateDays,
                    'early_days' => $earlyDays,
                ]
            ];
        });

        // Export CSV if requested
        if ($request->has('export') && $request->export == 'csv') {
            return $this->exportLogsCSV($logsData, $startDate->daysInMonth, $month, $year);
        }

        $locations = Location::active()->get();
        $departments = Department::active()->get();

        return view('reports.logs', [
            'logsData' => $logsData,
            'month' => $month,
            'year' => $year,
            'daysInMonth' => $startDate->daysInMonth,
            'locations' => $locations,
            'departments' => $departments,
            'locationId' => $locationId,
            'departmentId' => $departmentId,
        ]);
    }

    private function exportLogsCSV($logsData, $daysInMonth, $month, $year)
    {
        $filename = "list_of_logs_{$month}_{$year}.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        return response()->stream(function () use ($logsData, $daysInMonth) {
            $handle = fopen('php://output', 'w');

            // Header Row
            $header = ['Emp ID', 'Name', 'Department'];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $header[] = $d;
            }
            $header = array_merge($header, ['Present', 'Absent', 'WO', 'Leave', 'Total Hrs', 'OT']);
            fputcsv($handle, $header);

            // Data Rows
            foreach ($logsData as $row) {
                $data = [
                    $row->employee->employee_id,
                    $row->employee->name,
                    $row->employee->department->name ?? '-',
                ];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $data[] = $row->days[$d]->status;
                }
                $data = array_merge($data, [
                    $row->summary->present,
                    $row->summary->absent,
                    $row->summary->weekly_off,
                    $row->summary->leave,
                    $row->summary->total_duration,
                    $row->summary->total_ot,
                ]);
                fputcsv($handle, $data);
            }
            fclose($handle);
        }, 200, $headers);
    }

    private function minutesToTime($minutes)
    {
        if ($minutes <= 0)
            return '00:00';
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
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
