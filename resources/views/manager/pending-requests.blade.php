<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pending Leave Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($pendingRequests->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No pending requests</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">All requests from your team have been reviewed.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($pendingRequests as $request)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <!-- Employee Info -->
                                            <div class="flex items-center mb-2">
                                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                                    {{ substr($request->user->name, 0, 1) }}
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ $request->user->name }}
                                                    </h3>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        Submitted {{ $request->submitted_at->diffForHumans() }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Request Details -->
                                            <div class="ml-13 space-y-2">
                                                <div class="flex items-center text-sm">
                                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                    </svg>
                                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ ucwords(str_replace('_', ' ', $request->leave_type)) }}</span>
                                                </div>

                                                <div class="flex items-center text-sm">
                                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <span class="text-gray-700 dark:text-gray-300">
                                                        {{ $request->start_date->format('M d, Y') }} - {{ $request->end_date->format('M d, Y') }}
                                                        <span class="text-gray-500 dark:text-gray-400">({{ $request->start_date->diffInDays($request->end_date) + 1 }} days)</span>
                                                    </span>
                                                </div>

                                                @if ($request->employee_notes)
                                                    <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded text-sm text-gray-700 dark:text-gray-300">
                                                        <span class="font-medium">Note:</span> {{ Str::limit($request->employee_notes, 100) }}
                                                    </div>
                                                @endif

                                                <!-- Conflict Warnings -->
                                                @if (!empty($request->conflicts))
                                                    @foreach ($request->conflicts as $conflict)
                                                        <div class="mt-2 p-3 {{ $conflict['severity'] === 'high' ? 'bg-red-50 dark:bg-red-900 border-red-200 dark:border-red-700' : 'bg-yellow-50 dark:bg-yellow-900 border-yellow-200 dark:border-yellow-700' }} border rounded">
                                                            <div class="flex">
                                                                <svg class="h-5 w-5 {{ $conflict['severity'] === 'high' ? 'text-red-400' : 'text-yellow-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                </svg>
                                                                <div class="ml-3">
                                                                    <p class="text-sm font-medium {{ $conflict['severity'] === 'high' ? 'text-red-800 dark:text-red-200' : 'text-yellow-800 dark:text-yellow-200' }}">
                                                                        {{ $conflict['message'] }}
                                                                    </p>
                                                                    <div class="mt-1 text-sm {{ $conflict['severity'] === 'high' ? 'text-red-700 dark:text-red-300' : 'text-yellow-700 dark:text-yellow-300' }}">
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
                                            </div>
                                        </div>

                                        <!-- Action Button -->
                                        <div class="ml-4">
                                            <a href="{{ route('manager.show-request', $request) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 dark:hover:bg-blue-600 focus:bg-blue-700 dark:focus:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                Review
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $pendingRequests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
