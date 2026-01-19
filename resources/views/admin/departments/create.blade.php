<x-app-layout>
    @section('title', 'Add Department')
    
    <x-slot name="header">
        Add Department
    </x-slot>
    
    <div class="card bg-base-100 shadow-lg border border-base-300 max-w-2xl">
        <div class="card-body">
            <form action="{{ route('admin.departments.store') }}" method="POST">
                @csrf
                
                @if($errors->any())
                    <div class="alert alert-error mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Department Name *</span></label>
                        <input type="text" name="name" class="input input-bordered" 
                               value="{{ old('name') }}" placeholder="e.g., Human Resources" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Code *</span></label>
                        <input type="text" name="code" class="input input-bordered" 
                               value="{{ old('code') }}" placeholder="e.g., HR" maxlength="10" required>
                    </div>
                </div>
                
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Description</span></label>
                    <textarea name="description" class="textarea textarea-bordered" rows="3" 
                              placeholder="Brief description of the department">{{ old('description') }}</textarea>
                </div>
                
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Department Head</span></label>
                    <select name="manager_id" class="select select-bordered">
                        <option value="">No Manager</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                {{ $manager->name }} ({{ ucfirst($manager->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-control mb-6">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" checked>
                        <span class="label-text">Active Department</span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Department</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
