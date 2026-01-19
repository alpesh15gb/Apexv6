<x-app-layout>
    @section('title', 'Departments')

    <x-slot name="header">
        Departments
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Department
        </a>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-4">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($departments as $department)
            <div class="card bg-base-100 shadow-lg border border-base-300 hover:shadow-xl transition-shadow">
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="card-title text-lg">{{ $department->name }}</h3>
                            <span class="badge badge-ghost badge-sm">{{ $department->code }}</span>
                        </div>
                        <span class="badge {{ $department->is_active ? 'badge-success' : 'badge-error' }}">
                            {{ $department->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    @if($department->description)
                        <p class="text-sm text-base-content/60 mt-2">{{ Str::limit($department->description, 80) }}</p>
                    @endif

                    <div class="flex items-center gap-4 mt-4 text-sm">
                        <div class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/60" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>{{ $department->users_count }} employees</span>
                        </div>
                        @if($department->manager)
                            <div class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/60" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ $department->manager->name }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="card-actions justify-end mt-4">
                        <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-ghost btn-sm">Edit</a>
                        <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm text-error"
                                onclick="return confirm('Delete this department?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-base-content/60">
                No departments found. Create one to get started.
            </div>
        @endforelse
    </div>
</x-app-layout>