<?php

namespace App\Http\Controllers;

use App\Models\RegularizationRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Notifications\RegularizationApplied;
use App\Models\User;

class RegularizationController extends Controller
{
    /**
     * Store a new regularization request
     */
    public function store(Request $request)
    {
        // ... (validation code assumed same) ... 
        $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'punch_in_time' => 'required|date_format:H:i',
            'punch_out_time' => 'nullable|date_format:H:i|after:punch_in_time',
            'reason' => 'required|string|min:5|max:500',
        ]);

        $user = Auth::user();

        // Check if request already exists for this date
        $exists = RegularizationRequest::where('user_id', $user->id)
            ->whereDate('date', $request->date)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->with('error', 'A pending request already exists for this date.');
        }

        $regRequest = RegularizationRequest::create([
            'user_id' => $user->id,
            'date' => $request->date,
            'punch_in_time' => $request->punch_in_time,
            'punch_out_time' => $request->punch_out_time,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Send Notification to Manager
        $manager = $user->manager ?? User::where('role', 'admin')->first();
        if ($manager) {
            $manager->notify(new RegularizationApplied($regRequest));
        }

        return back()->with('success', 'Regularization request submitted successfully.');
    }

    /**
     * Approve a request
     */
    public function approve(RegularizationRequest $regularization)
    {
        $user = Auth::user();
        // Add Authorization check logic here or via Policy

        $regularization->update([
            'status' => 'approved',
            'approver_id' => $user->id,
        ]);

        // Update Attendance Record
        $attendance = Attendance::firstOrNew([
            'user_id' => $regularization->user_id,
            'date' => $regularization->date,
        ]);

        // Format times as full datetime strings
        $dateStr = $regularization->date->format('Y-m-d');

        $attendance->punch_in_time = $dateStr . ' ' . $regularization->punch_in_time;
        if ($regularization->punch_out_time) {
            $attendance->punch_out_time = $dateStr . ' ' . $regularization->punch_out_time;

            // Calc hours
            $start = Carbon::parse($attendance->punch_in_time);
            $end = Carbon::parse($attendance->punch_out_time);
            $attendance->total_hours = round($start->diffInMinutes($end) / 60, 2);
        }

        $attendance->status = 'present';
        // Or 'late' or whatever logic? For now 'present' is safe if they regularized.

        $attendance->save();

        // Notify Employee
        $regularization->user->notify(new RegularizationStatusUpdated($regularization));

        return back()->with('success', 'Request approved and attendance updated.');
    }

    /**
     * Reject a request
     */
    public function reject(Request $request, RegularizationRequest $regularization)
    {
        $regularization->update([
            'status' => 'rejected',
            'approver_id' => Auth::id(),
            'remarks' => $request->input('remarks'),
        ]);

        // Notify Employee
        $regularization->user->notify(new RegularizationStatusUpdated($regularization));

        return back()->with('success', 'Request rejected.');
    }
}
