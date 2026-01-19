<x-app-layout>
    @section('title', 'Edit Department')
    
    <x-slot name="header">
        Edit Department - {{ $department->name }}
    </x-slot>
    
    <div class="card bg-base-100 shadow-lg border border-base-300 max-w-2xl">
        <div class="card-body">
            <form action="{{ route('admin.departments.update', $department) }}" method="POST">
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
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Department Name *</span></label>
                        <input type="text" name="name" class="input input-bordered" 
                               value="{{ old('name', $department->name) }}" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Code *</span></label>
                        <input type="text" name="code" class="input input-bordered" 
                               value="{{ old('code', $department->code) }}" maxlength="10" required>
                    </div>
                </div>
                
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Description</span></label>
                    <textarea name="description" class="textarea textarea-bordered" rows="3">{{ old('description', $department->description) }}</textarea>
                </div>
                
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Department Head</span></label>
                    <select name="manager_id" class="select select-bordered">
                        <option value="">No Manager</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" {{ old('manager_id', $department->manager_id) == $manager->id ? 'selected' : '' }}>
                                {{ $manager->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-control mb-6">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" 
                               {{ old('is_active', $department->is_active) ? 'checked' : '' }}>
                        <span class="label-text">Active Department</span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Department</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
