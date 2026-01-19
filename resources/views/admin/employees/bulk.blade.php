@extends('layouts.app')

@section('title', 'Bulk Edit Employees')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Bulk Edit Employees</h1>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
                Back to List
            </a>
        </div>

        <!-- Filter Section (To narrow down list) -->
        <div class="card p-4">
            <form method="GET" action="{{ route('admin.employees.bulk') }}" class="flex flex-wrap gap-4 items-end">
                <div class="w-full sm:w-64">
                    <label for="department_id" class="label">Filter by Department</label>
                    <select name="department_id" id="filter_department_id" class="input w-full">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-64">
                    <label for="search" class="label">Search Name/ID</label>
                    <input type="text" name="search" class="input w-full" value="{{ request('search') }}"
                        placeholder="Search...">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if(request()->anyFilled(['department_id', 'search']))
                    <a href="{{ route('admin.employees.bulk') }}" class="btn btn-neutral">Clear</a>
                @endif
            </form>
        </div>

        <!-- Bulk Update Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
            x-data="{ 
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
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Update Selected Employees To:</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="label text-xs uppercase font-bold text-gray-500">Department</label>
                            <select name="department_id" class="select w-full text-sm">
                                <option value="">Do Not Change</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label text-xs uppercase font-bold text-gray-500">Designation</label>
                            <select name="designation_id" class="select w-full text-sm">
                                <option value="">Do Not Change</option>
                                @foreach($designations as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label text-xs uppercase font-bold text-gray-500">Location</label>
                            <select name="location_id" class="select w-full text-sm">
                                <option value="">Do Not Change</option>
                                @foreach($locations as $l)
                                    <option value="{{ $l->id }}">{{ $l->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label text-xs uppercase font-bold text-gray-500">Shift</label>
                            <select name="shift_id" class="select w-full text-sm">
                                <option value="">Do Not Change</option>
                                @foreach($shifts as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label text-xs uppercase font-bold text-gray-500">Manager</label>
                            <select name="manager_id" class="select w-full text-sm">
                                <option value="">Do Not Change</option>
                                @foreach($managers as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="text-sm text-gray-500">
                            Selected: <span x-text="selected.length" class="font-bold text-primary-600"></span> employees
                        </div>
                        <button type="submit" class="btn btn-primary" :disabled="selected.length === 0">
                            Apply Changes
                        </button>
                    </div>
                </div>

                <!-- Employee List -->
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
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
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="text-center">
                                        <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}"
                                            class="checkbox checkbox-sm checkbox-primary rounded" x-model="selected">
                                    </td>
                                    <td class="font-mono text-xs text-gray-500">{{ $emp->employee_id }}</td>
                                    <td>
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $emp->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $emp->email }}</div>
                                    </td>
                                    <td class="text-sm text-gray-600 dark:text-gray-300">{{ $emp->department->name ?? '-' }}
                                    </td>
                                    <td class="text-sm text-gray-600 dark:text-gray-300">{{ $emp->designation->name ?? '-' }}
                                    </td>
                                    <td class="text-xs text-gray-500">
                                        <div>Loc: {{ $emp->location->name ?? '-' }}</div>
                                        <div>Shf: {{ $emp->shift->name ?? 'GS' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-gray-500">
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
@endsection