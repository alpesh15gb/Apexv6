<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    /**
     * Display a listing of the shifts.
     */
    public function index()
    {
        $this->authorize('viewAny', Shift::class);

        $shifts = Shift::orderBy('name')->get();

        return view('admin.shifts.index', compact('shifts'));
    }

    /**
     * Show the form for creating a new shift.
     */
    public function create()
    {
        $this->authorize('create', Shift::class);

        return view('admin.shifts.create');
    }

    /**
     * Store a newly created shift in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Shift::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shifts,name',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'grace_period_minutes' => 'nullable|integer|min:0|max:60',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['grace_period_minutes'] = $validated['grace_period_minutes'] ?? 15;

        Shift::create($validated);

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Shift created successfully.');
    }

    /**
     * Show the form for editing the specified shift.
     */
    public function edit(Shift $shift)
    {
        $this->authorize('update', $shift);

        return view('admin.shifts.edit', compact('shift'));
    }

    /**
     * Update the specified shift in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        $this->authorize('update', $shift);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shifts,name,' . $shift->id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'grace_period_minutes' => 'nullable|integer|min:0|max:60',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $shift->update($validated);

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Shift updated successfully.');
    }

    /**
     * Remove the specified shift from storage.
     */
    public function destroy(Shift $shift)
    {
        $this->authorize('delete', $shift);

        // Check if shift is in use
        if ($shift->users()->count() > 0) {
            return redirect()->route('admin.shifts.index')
                ->with('error', 'Cannot delete shift. It is assigned to employees.');
        }

        $shift->delete();

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Shift deleted successfully.');
    }
}
