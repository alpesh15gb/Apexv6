<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    /**
     * Show leave application form
     */
    public function create()
    {
        $user = Auth::user();
        $leaveTypes = LeaveType::active()->get();

        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('user_id', $user->id)
            ->where('year', now()->year)
            ->get()
            ->keyBy('leave_type_id');

        return view('leave.apply', [
            'leaveTypes' => $leaveTypes,
            'leaveBalances' => $leaveBalances,
        ]);
    }

    /**
     * Store a new leave application
     */
    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'is_half_day' => 'nullable|boolean',
            'half_day_type' => 'nullable|in:first_half,second_half',
            'reason' => 'required|string|min:10|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);

        // Calculate total days
        $totalDays = $this->calculateLeaveDays($fromDate, $toDate, $request->is_half_day);

        // Check leave balance
        $balance = LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', now()->year)
            ->first();

        if (!$balance || $balance->available_balance < $totalDays) {
            return back()->with('error', 'Insufficient leave balance. Available: ' . ($balance?->available_balance ?? 0) . ' days');
        }

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }

        // Create leave request
        $leave = Leave::create([
            'user_id' => $user->id,
            'leave_type_id' => $request->leave_type_id,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'total_days' => $totalDays,
            'is_half_day' => $request->is_half_day ?? false,
            'half_day_type' => $request->half_day_type,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
            'applied_at' => now(),
        ]);

        // Update pending balance
        $balance->addPending($totalDays);

        return redirect()->route('leave.history')
            ->with('success', 'Leave application submitted successfully. Awaiting approval.');
    }

    /**
     * Show leave history
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $year = $request->input('year', now()->year);

        $leaves = Leave::with('leaveType', 'approver')
            ->where('user_id', $user->id)
            ->whereYear('from_date', $year)
            ->orderBy('applied_at', 'desc')
            ->get();

        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('user_id', $user->id)
            ->where('year', $year)
            ->get();

        return view('leave.history', [
            'leaves' => $leaves,
            'leaveBalances' => $leaveBalances,
            'year' => $year,
        ]);
    }

    /**
     * Cancel a pending leave
     */
    public function cancel(Leave $leave)
    {
        $user = Auth::user();

        if ($leave->user_id !== $user->id) {
            abort(403);
        }

        if (!$leave->canCancel()) {
            return back()->with('error', 'This leave cannot be cancelled.');
        }

        // Restore pending balance
        $balance = LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('year', now()->year)
            ->first();

        if ($balance) {
            $balance->removePending($leave->total_days);
        }

        $leave->update(['status' => 'cancelled']);

        return back()->with('success', 'Leave cancelled successfully.');
    }

    /**
     * Show pending approvals (for managers)
     */
    public function approvals()
    {
        $user = Auth::user();

        if (!$user->isManager() && !$user->hasAdminAccess()) {
            abort(403);
        }

        // Get pending leaves from subordinates or all (for HR)
        $query = Leave::with(['user', 'leaveType'])
            ->where('status', 'pending');

        if ($user->isManager() && !$user->hasAdminAccess()) {
            $subordinateIds = $user->subordinates->pluck('id');
            $query->whereIn('user_id', $subordinateIds);
        }

        $pendingLeaves = $query->orderBy('applied_at', 'asc')->get();

        return view('leave.approvals', [
            'pendingLeaves' => $pendingLeaves,
        ]);
    }

    /**
     * Approve a leave
     */
    public function approve(Leave $leave)
    {
        $user = Auth::user();

        if (!$this->canApprove($user, $leave)) {
            abort(403);
        }

        $leave->approve($user->id);

        // Convert pending to used in balance
        $balance = LeaveBalance::where('user_id', $leave->user_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('year', $leave->from_date->year)
            ->first();

        if ($balance) {
            $balance->convertPendingToUsed($leave->total_days);
        }

        return back()->with('success', 'Leave approved successfully.');
    }

    /**
     * Reject a leave
     */
    public function reject(Request $request, Leave $leave)
    {
        $user = Auth::user();

        if (!$this->canApprove($user, $leave)) {
            abort(403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        $leave->reject($user->id, $request->rejection_reason);

        // Restore pending balance
        $balance = LeaveBalance::where('user_id', $leave->user_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('year', $leave->from_date->year)
            ->first();

        if ($balance) {
            $balance->removePending($leave->total_days);
        }

        return back()->with('success', 'Leave rejected.');
    }

    /**
     * Check if user can approve this leave
     */
    private function canApprove($user, $leave): bool
    {
        if ($user->hasAdminAccess()) {
            return true;
        }

        if ($user->isManager()) {
            return $user->subordinates->contains('id', $leave->user_id);
        }

        return false;
    }

    /**
     * Calculate leave days excluding weekends
     */
    private function calculateLeaveDays(Carbon $from, Carbon $to, bool $isHalfDay = false): float
    {
        if ($isHalfDay) {
            return 0.5;
        }

        $days = 0;
        $current = $from->copy();

        while ($current->lte($to)) {
            if (!$current->isWeekend()) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }
}
