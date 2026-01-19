<x-app-layout>
    @section('title', 'Leave History')

    <x-slot name="header">
        Leave History
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('leave.apply') }}" class="btn btn-primary btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Apply for Leave
        </a>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Leave Balances Summary -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @forelse($leaveBalances as $balance)
            <div class="stat bg-base-100 rounded-lg shadow border border-base-300 p-4">
                <div class="stat-title text-xs">{{ $balance->leaveType->name }}</div>
                <div class="stat-value text-xl {{ $balance->available_balance > 0 ? 'text-success' : 'text-error' }}">
                    {{ $balance->available_balance }}
                </div>
                <div class="stat-desc">of {{ $balance->total_entitlement }} days</div>
            </div>
        @empty
            <div class="col-span-4 text-center text-base-content/60 py-4">
                No leave balances configured for {{ $year }}
            </div>
        @endforelse
    </div>

    <!-- Leave History Table -->
    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Leave Applications - {{ $year }}
                </h3>
                <select class="select select-bordered select-sm"
                    onchange="window.location.href='{{ route('leave.history') }}?year=' + this.value">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Applied On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                            <tr>
                                <td>
                                    <span class="badge badge-outline">{{ $leave->leaveType->code }}</span>
                                </td>
                                <td>{{ $leave->from_date->format('d M Y') }}</td>
                                <td>{{ $leave->to_date->format('d M Y') }}</td>
                                <td>
                                    {{ $leave->total_days }}
                                    @if($leave->is_half_day)
                                        <span
                                            class="text-xs text-base-content/60">({{ $leave->half_day_type == 'first_half' ? '1st' : '2nd' }}
                                            half)</span>
                                    @endif
                                </td>
                                <td class="max-w-xs truncate" title="{{ $leave->reason }}">
                                    {{ Str::limit($leave->reason, 40) }}</td>
                                <td>
                                    <span class="badge badge-{{ $leave->status_color }}">{{ $leave->status_label }}</span>
                                </td>
                                <td>{{ $leave->applied_at->format('d M Y') }}</td>
                                <td>
                                    @if($leave->canCancel())
                                        <form action="{{ route('leave.cancel', $leave) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Are you sure you want to cancel this leave?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-xs text-error">Cancel</button>
                                        </form>
                                    @elseif($leave->isRejected() && $leave->rejection_reason)
                                        <button class="btn btn-ghost btn-xs"
                                            onclick="alert('Rejection Reason: {{ $leave->rejection_reason }}')">
                                            View Reason
                                        </button>
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-base-content/60 py-8">
                                    No leave applications found for {{ $year }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>