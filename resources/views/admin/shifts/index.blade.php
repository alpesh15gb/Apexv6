<x-app-layout>
    @section('title', 'Shifts')

    <x-slot name="header">
        Shift Management
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Shift
        </a>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200">
                            <th>Name</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Grace Period</th>
                            <th>Status</th>
                            <th>Employees</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $shift)
                            <tr>
                                <td class="font-medium">{{ $shift->name }}</td>
                                <td class="font-mono">{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}</td>
                                <td class="font-mono">{{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</td>
                                <td>{{ $shift->grace_period_minutes ?? 15 }} min</td>
                                <td>
                                    @if($shift->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-ghost">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $shift->users_count ?? $shift->users()->count() }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.shifts.edit', $shift) }}" class="btn btn-ghost btn-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.shifts.destroy', $shift) }}" method="POST"
                                            onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-xs text-error">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-base-content/60">
                                    No shifts found. Click "Add Shift" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>