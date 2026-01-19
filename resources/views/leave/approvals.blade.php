<x-app-layout>
    @section('title', 'Leave Approvals')

    <x-slot name="header">
        Pending Leave Approvals
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

    <div class="card bg-base-100 shadow-lg border border-base-300">
        <div class="card-body">
            <h3 class="card-title mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Pending Requests ({{ $pendingLeaves->count() }})
            </h3>

            @if($pendingLeaves->count() > 0)
                <div class="space-y-4">
                    @foreach($pendingLeaves as $leave)
                        <div class="border border-base-300 rounded-lg p-4 hover:bg-base-50 transition-colors">
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="avatar placeholder">
                                            <div class="bg-primary text-primary-content rounded-full w-10">
                                                <span>{{ substr($leave->user->name, 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold">{{ $leave->user->name }}</h4>
                                            <p class="text-sm text-base-content/60">
                                                {{ $leave->user->designation?->name ?? 'Employee' }} •
                                                {{ $leave->user->department?->name ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mt-3">
                                        <div>
                                            <span class="text-base-content/60">Type:</span>
                                            <span class="badge badge-outline ml-1">{{ $leave->leaveType->code }}</span>
                                        </div>
                                        <div>
                                            <span class="text-base-content/60">Duration:</span>
                                            <span class="font-medium ml-1">{{ $leave->total_days }} day(s)</span>
                                        </div>
                                        <div>
                                            <span class="text-base-content/60">Dates:</span>
                                            <span class="ml-1">{{ $leave->date_range }}</span>
                                        </div>
                                        <div>
                                            <span class="text-base-content/60">Applied:</span>
                                            <span class="ml-1">{{ $leave->applied_at->diffForHumans() }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-3 p-3 bg-base-200 rounded-lg">
                                        <span class="text-base-content/60 text-sm">Reason:</span>
                                        <p class="text-sm mt-1">{{ $leave->reason }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-row lg:flex-col gap-2">
                                    <form action="{{ route('leave.approve', $leave) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm gap-1 w-full"
                                            onclick="return confirm('Approve this leave request?')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Approve
                                        </button>
                                    </form>

                                    <button class="btn btn-error btn-sm gap-1" onclick="showRejectModal({{ $leave->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-success mb-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-success">All Caught Up!</h3>
                    <p class="text-base-content/60 mt-2">No pending leave requests to review.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Reject Modal -->
    <dialog id="reject-modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Reject Leave Request</h3>
            <form id="reject-form" method="POST">
                @csrf
                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Reason for Rejection *</span>
                    </label>
                    <textarea name="rejection_reason" class="textarea textarea-bordered h-24"
                        placeholder="Please provide a reason..." required></textarea>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-ghost"
                        onclick="document.getElementById('reject-modal').close()">Cancel</button>
                    <button type="submit" class="btn btn-error">Reject Leave</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <script>
        function showRejectModal(leaveId) {
            document.getElementById('reject-form').action = '/leave/' + leaveId + '/reject';
            document.getElementById('reject-modal').showModal();
        }
    </script>
</x-app-layout>