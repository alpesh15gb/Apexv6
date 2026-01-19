<x-app-layout>
    @section('title', 'Bulk Edit Employees')

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>Bulk Edit Employees</div>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary btn-sm">
                Back to List
            </a>
        </div>
    </x-slot>

    <!-- Content ($slot) -->
    <div class="space-y-6">

        <!-- Filter Section -->
        <div class="card bg-base-100 shadow border border-base-300">
            <div class="card-body py-4">
                <form method="GET" action="{{ route('admin.employees.bulk') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="form-control w-full sm:w-64">
                        <label for="filter_department_id" class="label"><span class="label-text">Filter by
                                Department</span></label>
                        <select name="department_id" id="filter_department_id"
                            class="select select-bordered select-sm w-full">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control w-full sm:w-64">
                        <label for="search" class="label"><span class="label-text">Search Name/ID</span></label>
                        <input type="text" name="search" class="input input-bordered input-sm w-full"
                            value="{{ request('search') }}" placeholder="Search...">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->anyFilled(['department_id', 'search']))
                        <a href="{{ route('admin.employees.bulk') }}" class="btn btn-ghost btn-sm">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Bulk Update Form -->
        <div class="card bg-base-100 shadow-lg border border-base-300 overflow-hidden" x-data="{ 
                selected: [], 
                toggleAll() { 
                    if (this.selected.length === {{ $employees->count() }}) { 
                        this.selected = []; 
                    } else { 
                        this.selected = [
                            @foreach($employees as $emp) '{{ $emp->id }}', @endforeach
                        ]; 
                    } 
                } 
             }">

            <form method="POST" action="{{ route('admin.employees.bulk-update') }}">
                @csrf

                <!-- Update Controls -->
                <div class="p-6 border-b border-base-200 bg-base-200/50">
                    <h3 class="text-lg font-semibold mb-4">Update Selected Employees To:</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div class="form-control">
                            <label class="label text-xs uppercase font-bold text-base-content/60">Department</label>
                            <select name="department_id" class="select select-bordered select-sm w-full">
                                <option value="">Do Not Change</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label text-xs uppercase font-bold text-base-content/60">Designation</label>
                            <select name="designation_id" class="select select-bordered select-sm w-full">
                                <option value="">Do Not Change</option>
                                @foreach($designations as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label text-xs uppercase font-bold text-base-content/60">Location</label>
                            <select name="location_id" class="select select-bordered select-sm w-full">
                                <option value="">Do Not Change</option>
                                @foreach($locations as $l)
                                    <option value="{{ $l->id }}">{{ $l->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label text-xs uppercase font-bold text-base-content/60">Shift</label>
                            <select name="shift_id" class="select select-bordered select-sm w-full">
                                <option value="">Do Not Change</option>
                                @foreach($shifts as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label text-xs uppercase font-bold text-base-content/60">Manager</label>
                            <select name="manager_id" class="select select-bordered select-sm w-full">
                                <option value="">Do Not Change</option>
                                @foreach($managers as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-base-300 pt-4">
                        <div class="text-sm text-base-content/70">
                            Selected: <span x-text="selected.length" class="font-bold text-primary"></span> employees
                        </div>
                        <button type="submit" class="btn btn-primary" :disabled="selected.length === 0">
                            Apply Changes
                        </button>
                    </div>
                </div>

                <!-- Employee List -->
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr class="bg-base-200">
                                <th class="w-10 text-center">
                                    <input type="checkbox" class="checkbox checkbox-sm checkbox-primary rounded"
                                        @click="toggleAll()"
                                        :checked="selected.length > 0 && selected.length === {{ $employees->count() }}">
                                </th>
                                <th>ID</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Location/Shift</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $emp)
                                <tr class="hover">
                                    <td class="text-center">
                                        <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}"
                                            class="checkbox checkbox-sm checkbox-primary rounded" x-model="selected">
                                    </td>
                                    <td class="font-mono text-xs">{{ $emp->employee_id }}</td>
                                    <td>
                                        <div class="font-medium">{{ $emp->name }}</div>
                                        <div class="text-xs text-base-content/60">{{ $emp->email }}</div>
                                    </td>
                                    <td class="text-sm">{{ $emp->department->name ?? '-' }}</td>
                                    <td class="text-sm">{{ $emp->designation->name ?? '-' }}</td>
                                    <td class="text-xs">
                                        <div>Loc: {{ $emp->location->name ?? '-' }}</div>
                                        <div>Shf: {{ $emp->shift->name ?? 'GS' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-base-content/60">
                                        No employees found matching the filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>