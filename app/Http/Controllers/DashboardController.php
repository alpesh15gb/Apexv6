<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Company-wide stats for Admin/Manager
        $totalEmployees = User::where('role', '!=', 'admin')->count();

        // Present Today: unique users with attendance records today
        $presentToday = Attendance::whereDate('date', $today)
            ->distinct('user_id')
            ->count();

        // Late Today
        $lateToday = Attendance::whereDate('date', $today)
            ->where('status', 'late')
            ->count();

        // On Leave Today: approved leave requests covering today
        $onLeaveToday = Leave::where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        // Recent Activity: latest punches from anyone
        $recentActivity = Attendance::with('user')
            ->whereDate('date', $today)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Upcoming holidays (company wide)
        $upcomingHolidays = Holiday::active()
            ->upcoming(60)
            ->orderBy('date')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'totalEmployees' => $totalEmployees,
            'presentToday' => $presentToday,
            'lateToday' => $lateToday,
            'onLeaveToday' => $onLeaveToday,
            'recentActivity' => $recentActivity,
            'upcomingHolidays' => $upcomingHolidays,
        ]);
    }

    /**
     * Get employees by status for drill-down modal
     */
    public function getEmployeesByStatus(Request $request, string $status)
    {
        $today = Carbon::today();

        switch ($status) {
            case 'total':
                $employees = User::with(['department', 'designation'])
                    ->where('role', '!=', 'admin')
                    ->orderBy('name')
                    ->get();
                break;

            case 'present':
                $userIds = Attendance::whereDate('date', $today)
                    ->whereIn('status', ['present', 'late', 'half_day'])
                    ->pluck('user_id');
                $employees = User::with(['department', 'designation'])
                    ->whereIn('id', $userIds)
                    ->orderBy('name')
                    ->get();
                break;

            case 'late':
                $userIds = Attendance::whereDate('date', $today)
                    ->where('status', 'late')
                    ->pluck('user_id');
                $employees = User::with(['department', 'designation'])
                    ->whereIn('id', $userIds)
                    ->orderBy('name')
                    ->get();
                break;

            case 'leave':
                $userIds = Leave::where('status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->pluck('user_id');
                $employees = User::with(['department', 'designation'])
                    ->whereIn('id', $userIds)
                    ->orderBy('name')
                    ->get();
                break;

            case 'absent':
                // Get all employees minus present and on-leave
                $presentIds = Attendance::whereDate('date', $today)->pluck('user_id');
                $leaveIds = Leave::where('status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->pluck('user_id');
                $excludeIds = $presentIds->merge($leaveIds)->unique();

                $employees = User::with(['department', 'designation'])
                    ->where('role', '!=', 'admin')
                    ->whereNotIn('id', $excludeIds)
                    ->orderBy('name')
                    ->get();
                break;

            default:
                $employees = collect();
        }

        // Return JSON for AJAX
        if ($request->wantsJson()) {
            $today = Carbon::today();

            return response()->json([
                'status' => $status,
                'count' => $employees->count(),
                'employees' => $employees->map(function ($e) use ($today) {
                    // Get today's attendance for this employee
                    $attendance = Attendance::where('user_id', $e->id)
                        ->whereDate('date', $today)
                        ->first();

                    return [
                        'id' => $e->id,
                        'name' => $e->name,
                        'employee_id' => $e->employee_id,
                        'punch_in' => $attendance?->punch_in_time
                            ? Carbon::parse($attendance->punch_in_time)->format('h:i A')
                            : '-',
                        'punch_out' => $attendance?->punch_out_time
                            ? Carbon::parse($attendance->punch_out_time)->format('h:i A')
                            : '-',
                        'status' => $attendance?->status ?? 'absent',
                    ];
                })
            ]);
        }

        // Return view for direct access
        return view('dashboard.employees', [
            'status' => $status,
            'employees' => $employees,
        ]);
    }
}
