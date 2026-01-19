<x-app-layout>
    @section('title', 'Edit Employee')
    
    <x-slot name="header">
        Edit Employee - {{ $employee->name }}
    </x-slot>
    
    <div class="card bg-base-100 shadow-lg border border-base-300 max-w-4xl">
        <div class="card-body">
            <form action="{{ route('admin.employees.update', $employee) }}" method="POST">
                @csrf
                @method('PUT')
                
                @if($errors->any())
                    <div class="alert alert-error mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- Basic Info -->
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Basic Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Full Name *</span></label>
                        <input type="text" name="name" class="input input-bordered" 
                               value="{{ old('name', $employee->name) }}" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Employee ID *</span></label>
                        <input type="text" name="employee_id" class="input input-bordered" 
                               value="{{ old('employee_id', $employee->employee_id) }}" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Email *</span></label>
                        <input type="email" name="email" class="input input-bordered" 
                               value="{{ old('email', $employee->email) }}" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Phone</span></label>
                        <input type="text" name="phone" class="input input-bordered" 
                               value="{{ old('phone', $employee->phone) }}">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Gender</span></label>
                        <select name="gender" class="select select-bordered">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $employee->gender) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Joining Date *</span></label>
                        <input type="date" name="joining_date" class="input input-bordered" 
                               value="{{ old('joining_date', $employee->joining_date?->format('Y-m-d')) }}" required>
                    </div>
                </div>
                
                <!-- Work Info -->
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Work Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Department *</span></label>
                        <select name="department_id" class="select select-bordered" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Designation *</span></label>
                        <select name="designation_id" class="select select-bordered" required>
                            @foreach($designations as $desig)
                                <option value="{{ $desig->id }}" {{ old('designation_id', $employee->designation_id) == $desig->id ? 'selected' : '' }}>
                                    {{ $desig->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Location *</span></label>
                        <select name="location_id" class="select select-bordered" required>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('location_id', $employee->location_id) == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Shift</span></label>
                        <select name="shift_id" class="select select-bordered">
                            <option value="">Default Shift</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id', $employee->shift_id) == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Role *</span></label>
                        <select name="role" class="select select-bordered" required>
                            <option value="employee" {{ old('role', $employee->role) == 'employee' ? 'selected' : '' }}>Employee</option>
                            <option value="manager" {{ old('role', $employee->role) == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="hr_admin" {{ old('role', $employee->role) == 'hr_admin' ? 'selected' : '' }}>HR Admin</option>
                            <option value="super_admin" {{ old('role', $employee->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Reporting Manager</span></label>
                        <select name="manager_id" class="select select-bordered">
                            <option value="">No Manager</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ old('manager_id', $employee->manager_id) == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="form-control mb-6">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" 
                               {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                        <span class="label-text">Active Employee</span>
                    </label>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
