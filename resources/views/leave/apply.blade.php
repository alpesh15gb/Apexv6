<x-app-layout>
    @section('title', 'Apply for Leave')
    
    <x-slot name="header">
        Apply for Leave
    </x-slot>
    
    <!-- Leave Balances -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach($leaveTypes as $type)
            @php
                $balance = $leaveBalances[$type->id] ?? null;
                $available = $balance ? $balance->available_balance : $type->total_days_per_year;
            @endphp
            <div class="stat bg-base-100 rounded-lg shadow border border-base-300 p-4">
                <div class="stat-title text-xs">{{ $type->name }} ({{ $type->code }})</div>
                <div class="stat-value text-xl {{ $available > 0 ? 'text-success' : 'text-error' }}">
                    {{ $available }}
                </div>
                <div class="stat-desc">days available</div>
            </div>
        @endforeach
    </div>
    
    <!-- Application Form -->
    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <h3 class="card-title mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Leave Application Form
            </h3>
            
            @if(session('error'))
                <div class="alert alert-error mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            
            <form action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Leave Type -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Leave Type *</span>
                        </label>
                        <select name="leave_type_id" class="select select-bordered w-full @error('leave_type_id') select-error @enderror" required>
                            <option value="">Select Leave Type</option>
                            @foreach($leaveTypes as $type)
                                @php $balance = $leaveBalances[$type->id] ?? null; @endphp
                                <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}
                                    data-balance="{{ $balance?->available_balance ?? $type->total_days_per_year }}">
                                    {{ $type->name }} ({{ $balance?->available_balance ?? $type->total_days_per_year }} days available)
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>
                    
                    <!-- Half Day Toggle -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Leave Duration</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <label class="label cursor-pointer gap-2">
                                <input type="checkbox" name="is_half_day" value="1" class="checkbox checkbox-primary" 
                                       onchange="toggleHalfDay(this)" {{ old('is_half_day') ? 'checked' : '' }}>
                                <span class="label-text">Half Day</span>
                            </label>
                            <select name="half_day_type" id="half_day_type" class="select select-bordered select-sm" 
                                    {{ old('is_half_day') ? '' : 'disabled' }}>
                                <option value="first_half" {{ old('half_day_type') == 'first_half' ? 'selected' : '' }}>First Half</option>
                                <option value="second_half" {{ old('half_day_type') == 'second_half' ? 'selected' : '' }}>Second Half</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- From Date -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">From Date *</span>
                        </label>
                        <input type="date" name="from_date" class="input input-bordered @error('from_date') input-error @enderror" 
                               value="{{ old('from_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                        @error('from_date')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>
                    
                    <!-- To Date -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">To Date *</span>
                        </label>
                        <input type="date" name="to_date" class="input input-bordered @error('to_date') input-error @enderror" 
                               value="{{ old('to_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                        @error('to_date')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>
                </div>
                
                <!-- Reason -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Reason *</span>
                    </label>
                    <textarea name="reason" class="textarea textarea-bordered h-24 @error('reason') textarea-error @enderror" 
                              placeholder="Please provide a detailed reason for your leave request..." required>{{ old('reason') }}</textarea>
                    @error('reason')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>
                
                <!-- Attachment -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Attachment (Optional)</span>
                        <span class="label-text-alt">PDF, JPG, PNG (max 2MB)</span>
                    </label>
                    <input type="file" name="attachment" class="file-input file-input-bordered w-full" accept=".pdf,.jpg,.jpeg,.png">
                    @error('attachment')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>
                
                <!-- Submit -->
                <div class="flex justify-end gap-2 mt-6">
                    <a href="{{ route('leave.history') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function toggleHalfDay(checkbox) {
            document.getElementById('half_day_type').disabled = !checkbox.checked;
        }
    </script>
</x-app-layout>
