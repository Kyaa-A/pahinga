<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manager Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Pending Requests Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Pending Requests
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $pendingCount }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('manager.pending-requests') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                Review now →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Approved This Month Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Approved This Month
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $approvedThisMonth }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Size Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Team Members
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $teamSize }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('manager.team-calendar') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                View calendar →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Pending Requests -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Pending Requests</h3>
                            @if ($pendingCount > 0)
                                <a href="{{ route('manager.pending-requests') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                    View all
                                </a>
                            @endif
                        </div>

                        @if ($recentRequests->isEmpty())
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No pending requests</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($recentRequests as $request)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $request->user->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ ucwords(str_replace('_', ' ', $request->leave_type)) }} • {{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d') }}
                                            </p>
                                        </div>
                                        <a href="{{ route('manager.show-request', $request) }}" class="ml-4 inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-blue-700 dark:text-blue-400 bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800">
                                            Review
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Upcoming Team Leaves -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Upcoming Team Leaves</h3>
                            <a href="{{ route('manager.team-calendar') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                Calendar
                            </a>
                        </div>

                        @if ($upcomingLeaves->isEmpty())
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No upcoming leaves</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($upcomingLeaves as $leave)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $leave->user->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }} ({{ $leave->start_date->diffInDays($leave->end_date) + 1 }} days)
                                            </p>
                                        </div>
                                        <span class="ml-4 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Approved
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
