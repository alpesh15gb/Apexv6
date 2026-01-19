<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    private function checkAdminAccess(): void
    {
        if (!Auth::user()?->hasAdminAccess()) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function index()
    {
        $this->checkAdminAccess();

        $locations = Location::withCount('users')
            ->orderBy('name')
            ->get();

        return view('admin.locations.index', [
            'locations' => $locations,
        ]);
    }

    public function create()
    {
        $this->checkAdminAccess();

        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
            'code' => 'required|string|max:10|unique:locations,code',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'geofence_radius' => 'nullable|integer|min:0|max:10000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Location::create($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', "Location '{$validated['name']}' created successfully.");
    }

    public function edit(Location $location)
    {
        $this->checkAdminAccess();

        return view('admin.locations.edit', [
            'location' => $location,
        ]);
    }

    public function update(Request $request, Location $location)
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id,
            'code' => 'required|string|max:10|unique:locations,code,' . $location->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'geofence_radius' => 'nullable|integer|min:0|max:10000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $location->update($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', "Location '{$location->name}' updated successfully.");
    }

    public function destroy(Location $location)
    {
        $this->checkAdminAccess();

        if ($location->users()->count() > 0) {
            return back()->with('error', 'Cannot delete location with assigned employees.');
        }

        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', "Location deleted successfully.");
    }
}
