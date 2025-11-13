<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('manager.pending-requests') }}" class="mr-4 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Review Leave Request') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Conflict Warnings (if any) -->
            @if (!empty($conflicts))
                @foreach ($conflicts as $conflict)
                    <div class="mb-4 p-4 {{ $conflict['severity'] === 'high' ? 'bg-red-50 dark:bg-red-900 border-red-400 dark:border-red-600' : 'bg-yellow-50 dark:bg-yellow-900 border-yellow-400 dark:border-yellow-600' }} border rounded-lg">
                        <div class="flex">
                            <svg class="h-6 w-6 {{ $conflict['severity'] === 'high' ? 'text-red-400' : 'text-yellow-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium {{ $conflict['severity'] === 'high' ? 'text-red-800 dark:text-red-200' : 'text-yellow-800 dark:text-yellow-200' }}">
                                    Staffing Conflict Detected
                                </h3>
                                <p class="mt-1 text-sm {{ $conflict['severity'] === 'high' ? 'text-red-700 dark:text-red-300' : 'text-yellow-700 dark:text-yellow-300' }}">
                                    {{ $conflict['message'] }}
                                </p>
                                <div class="mt-2 text-sm {{ $conflict['severity'] === 'high' ? 'text-red-700 dark:text-red-300' : 'text-yellow-700 dark:text-yellow-300' }}">
                                    <ul class="list-disc list-inside">
                                        @foreach ($conflict['details'] as $detail)
                                            <li>{{ $detail['employee'] }}: {{ $detail['dates'] }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Request Details Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <!-- Employee Info Header -->
                    <div class="flex items-center mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-semibold">
                            {{ substr($leaveRequest->user->name, 0, 1) }}
                        </div>
                        <div class="ml-4">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $leaveRequest->user->name }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $leaveRequest->user->email }}
                            </p>
                        </div>
                    </div>

                    <!-- Request Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-900 dark:text-gray-100">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Leave Type</h3>
                            <p class="text-lg font-semibold">{{ ucwords(str_replace('_', ' ', $leaveRequest->leave_type)) }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Duration</h3>
                            <p class="text-lg font-semibold">{{ $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1 }} days</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Start Date</h3>
                            <p class="text-lg">{{ $leaveRequest->start_date->format('l, F j, Y') }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">End Date</h3>
                            <p class="text-lg">{{ $leaveRequest->end_date->format('l, F j, Y') }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Submitted</h3>
                            <p class="text-lg">{{ $leaveRequest->submitted_at->format('M d, Y h:i A') }} ({{ $leaveRequest->submitted_at->diffForHumans() }})</p>
                        </div>

                        @if ($leaveRequest->employee_notes)
                            <div class="md:col-span-2">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Employee's Notes</h3>
                                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <p class="text-base whitespace-pre-wrap">{{ $leaveRequest->employee_notes }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Forms -->
            @if ($leaveRequest->isPending())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Approve Form -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-green-600 dark:text-green-400 mb-4">Approve Request</h3>
                            <form method="POST" action="{{ route('manager.approve', $leaveRequest) }}">
                                @csrf
                                <div class="mb-4">
                                    <label for="approve_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Notes (Optional)
                                    </label>
                                    <textarea id="approve_notes" name="manager_notes" rows="3"
                                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-green-500 dark:focus:border-green-600 focus:ring-green-500 dark:focus:ring-green-600"
                                              placeholder="Add any comments for the employee..."></textarea>
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 dark:bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 dark:hover:bg-green-600 focus:bg-green-700 dark:focus:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Approve Request
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Deny Form -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4">Deny Request</h3>
                            <form method="POST" action="{{ route('manager.deny', $leaveRequest) }}" onsubmit="return confirm('Are you sure you want to deny this request?');">
                                @csrf
                                <div class="mb-4">
                                    <label for="deny_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Reason for Denial <span class="text-red-500">*</span>
                                    </label>
                                    <textarea id="deny_notes" name="manager_notes" rows="3" required
                                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-red-500 dark:focus:border-red-600 focus:ring-red-500 dark:focus:ring-red-600"
                                              placeholder="Please explain why this request is being denied..."></textarea>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Required: A reason must be provided to the employee</p>
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 dark:bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-600 focus:bg-red-700 dark:focus:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Deny Request
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <!-- Request Already Reviewed -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-gray-600 dark:text-gray-400">
                            This request has already been {{ $leaveRequest->status }}.
                        </p>
                        @if ($leaveRequest->manager_notes)
                            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Manager's Notes:</p>
                                <p class="text-base text-gray-900 dark:text-gray-100">{{ $leaveRequest->manager_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- History Timeline -->
            @if ($leaveRequest->history->isNotEmpty())
                <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Request History</h3>
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach ($leaveRequest->history as $historyItem)
                                    <li>
                                        <div class="relative pb-8">
                                            @if (!$loop->last)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div>
                                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                                            <span class="font-medium">{{ $historyItem->performedBy->name }}</span>
                                                            <span class="text-gray-500 dark:text-gray-400">{{ $historyItem->action }}</span>
                                                            this request
                                                        </div>
                                                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $historyItem->created_at->format('M d, Y h:i A') }}
                                                        </p>
                                                    </div>
                                                    @if ($historyItem->notes)
                                                        <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                                            {{ $historyItem->notes }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
