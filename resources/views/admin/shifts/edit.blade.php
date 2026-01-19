<x-app-layout>
    @section('title', 'Edit Shift')

    <x-slot name="header">
        Edit Shift: {{ $shift->name }}
    </x-slot>

    <div class="max-w-2xl">
        <div class="card bg-base-100 shadow-lg border border-base-300">
            <div class="card-body">
                <form action="{{ route('admin.shifts.update', $shift) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Shift Name</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $shift->name) }}"
                            class="input input-bordered @error('name') input-error @enderror"
                            placeholder="e.g., General Shift, Night Shift" required>
                        @error('name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Start Time</span>
                            </label>
                            <input type="time" name="start_time"
                                value="{{ old('start_time', \Carbon\Carbon::parse($shift->start_time)->format('H:i')) }}"
                                class="input input-bordered @error('start_time') input-error @enderror" required>
                            @error('start_time')
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">End Time</span>
                            </label>
                            <input type="time" name="end_time"
                                value="{{ old('end_time', \Carbon\Carbon::parse($shift->end_time)->format('H:i')) }}"
                                class="input input-bordered @error('end_time') input-error @enderror" required>
                            @error('end_time')
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>
                    </div>

                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Grace Period (minutes)</span>
                        </label>
                        <input type="number" name="grace_period_minutes"
                            value="{{ old('grace_period_minutes', $shift->grace_period_minutes ?? 15) }}"
                            class="input input-bordered @error('grace_period_minutes') input-error @enderror" min="0"
                            max="60">
                        <label class="label">
                            <span class="label-text-alt">Time allowed before marking as late</span>
                        </label>
                        @error('grace_period_minutes')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control mb-6">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" {{ old('is_active', $shift->is_active) ? 'checked' : '' }}>
                            <span class="label-text">Active</span>
                        </label>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="btn btn-primary">Update Shift</button>
                        <a href="{{ route('admin.shifts.index') }}" class="btn btn-ghost">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>