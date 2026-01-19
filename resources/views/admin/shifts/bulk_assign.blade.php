<x-app-layout>
    @section('title', 'Bulk Assign Shifts')

    <x-slot name="header">
        Bulk Assign Shifts
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Filter Sidebar -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-lg border border-base-300">
                <div class="card-body p-4">
                    <h3 class="font-bold text-lg mb-4">Filter Employees</h3>
                    <form action="{{ route('admin.shifts.bulk-assign') }}" method="GET">
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text">Department</span>
                            </label>
                            <select name="department_id" class="select select-bordered" onchange="this.form.submit()">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card bg-base-100 shadow-lg border border-base-300 mt-6">
                <div class="card-body p-4">
                    <h3 class="font-bold text-lg mb-4">Assign Shift</h3>
                    <div class="alert alert-info text-xs mb-4">
                        Select employees from the list and choose a shift below to update.
                    </div>
                    
                    <button type="submit" form="bulk-form" class="btn btn-primary w-full">Apply Shift Update</button>
                    <a href="{{ route('admin.shifts.index') }}" class="btn btn-ghost w-full mt-2">Cancel</a>
                </div>
            </div>
        </div>

        <!-- Employee List -->
        <div class="lg:col-span-3">
            @if(session('success'))
                <div class="alert alert-success mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card bg-base-100 shadow-lg border border-base-300">
                <div class="card-body">
                    <form id="bulk-form" action="{{ route('admin.shifts.bulk-update') }}" method="POST">
                        @csrf
                        
                        <div class="flex items-end gap-4 mb-6 bg-base-200 p-4 rounded-lg">
                            <div class="form-control w-full max-w-xs">
                                <label class="label">
                                    <span class="label-text font-bold">Select New Shift</span>
                                </label>
                                <select name="shift_id" class="select select-bordered" required>
                                    <option value="">Choose Shift...</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}">
                                            {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="pb-2">
                                <span class="text-sm opacity-70">Will be applied to selected employees</span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="table table-zebra w-full">
                                <thead>
                                    <tr>
                                        <th class="w-10">
                                            <label>
                                                <input type="checkbox" class="checkbox checkbox-sm" id="select-all" />
                                            </label>
                                        </th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Current Shift</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employees as $employee)
                                        <tr>
                                            <th>
                                                <label>
                                                    <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" class="checkbox checkbox-sm employee-checkbox" />
                                                </label>
                                            </th>
                                            <td class="font-mono text-xs">{{ $employee->employee_id }}</td>
                                            <td>
                                                <div class="font-bold">{{ $employee->name }}</div>
                                                <div class="text-xs opacity-50">{{ $employee->designation?->name }}</div>
                                            </td>
                                            <td>{{ $employee->department?->name }}</td>
                                            <td>
                                                @if($employee->shift)
                                                    <div class="badge badge-outline">{{ $employee->shift->name }}</div>
                                                    <div class="text-xs mt-1 font-mono">
                                                        {{ \Carbon\Carbon::parse($employee->shift->start_time)->format('H:i') }}
                                                    </div>
                                                @else
                                                    <span class="opacity-50 text-xs italic">None</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-8 opacity-50">No employees found matching filter</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.employee-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
</x-app-layout>
