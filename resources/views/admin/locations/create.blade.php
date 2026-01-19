<x-app-layout>
    @section('title', 'Add Location')
    
    <x-slot name="header">
        Add Location
    </x-slot>
    
    <div class="card bg-base-100 shadow-lg border border-base-300 max-w-3xl">
        <div class="card-body">
            <form action="{{ route('admin.locations.store') }}" method="POST">
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
                
                <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Location Name *</span></label>
                        <input type="text" name="name" class="input input-bordered" 
                               value="{{ old('name') }}" placeholder="e.g., Head Office" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Code *</span></label>
                        <input type="text" name="code" class="input input-bordered" 
                               value="{{ old('code') }}" placeholder="e.g., HO" maxlength="10" required>
                    </div>
                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text">Address</span></label>
                        <textarea name="address" class="textarea textarea-bordered" rows="2" 
                                  placeholder="Full street address">{{ old('address') }}</textarea>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">City</span></label>
                        <input type="text" name="city" class="input input-bordered" 
                               value="{{ old('city') }}" placeholder="e.g., Mumbai">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">State</span></label>
                        <input type="text" name="state" class="input input-bordered" 
                               value="{{ old('state') }}" placeholder="e.g., Maharashtra">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Country</span></label>
                        <input type="text" name="country" class="input input-bordered" 
                               value="{{ old('country', 'India') }}" placeholder="e.g., India">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Timezone</span></label>
                        <select name="timezone" class="select select-bordered">
                            <option value="Asia/Kolkata" {{ old('timezone', 'Asia/Kolkata') == 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                            <option value="UTC" {{ old('timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                            <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                        </select>
                    </div>
                </div>
                
                <h3 class="text-lg font-semibold mb-4">Geofencing (Optional)</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Latitude</span></label>
                        <input type="number" step="any" name="latitude" class="input input-bordered" 
                               value="{{ old('latitude') }}" placeholder="e.g., 19.0760">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Longitude</span></label>
                        <input type="number" step="any" name="longitude" class="input input-bordered" 
                               value="{{ old('longitude') }}" placeholder="e.g., 72.8777">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Geofence Radius (meters)</span></label>
                        <input type="number" name="geofence_radius" class="input input-bordered" 
                               value="{{ old('geofence_radius', 100) }}" placeholder="100" min="0" max="10000">
                    </div>
                </div>
                
                <div class="form-control mb-6">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" checked>
                        <span class="label-text">Active Location</span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.locations.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Location</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
