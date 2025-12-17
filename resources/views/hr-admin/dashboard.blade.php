<x-app-layout>
    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                {{-- Total Users --}}
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalUsers }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $totalEmployees }} employees, {{ $totalManagers }} managers
                                </p>
                            </div>
                            <div class="rounded-full bg-primary-100 dark:bg-primary-900/30 p-3">
                                <svg class="h-8 w-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pending Requests --}}
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Requests</p>
                                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $pendingRequests }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Awaiting manager review</p>
                            </div>
                            <div class="rounded-full bg-yellow-100 dark:bg-yellow-900/30 p-3">
                                <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Currently on Leave --}}
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Currently on Leave</p>
                                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $currentlyOnLeave }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Employees out today</p>
                            </div>
                            <div class="rounded-full bg-blue-100 dark:bg-blue-900/30 p-3">
                                <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Approved This Month --}}
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Approved This Month</p>
                                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $approvedThisMonth }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ now()->format('F Y') }}</p>
                            </div>
                            <div class="rounded-full bg-green-100 dark:bg-green-900/30 p-3">
                                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Leave Type Breakdown and Balance Summary --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {{-- Leave Type Breakdown --}}
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Leave Type Breakdown ({{ now()->year }})</h3>
                    </div>
                    <div class="p-6">
                        @if($leaveTypeBreakdown->count() > 0)
                            <div class="space-y-4">
                                @foreach($leaveTypeBreakdown as $breakdown)
                                    <div>
                                        <div class="mb-1 flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ $breakdown->leave_type->label() }}
                                            </span>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $breakdown->count }} requests ({{ $breakdown->total_days }} days)
                                            </span>
                                        </div>
                                        <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div class="h-2 rounded-full bg-primary-600 dark:bg-primary-500" style="width: {{ $leaveTypeBreakdown->sum('total_days') > 0 ? ($breakdown->total_days / $leaveTypeBreakdown->sum('total_days')) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">No leave requests approved this year yet.</p>
                        @endif
                    </div>
                </div>

                {{-- Balance Summary --}}
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Company-wide Balance Summary</h3>
                    </div>
                    <div class="p-6">
                        @if($balanceSummary->count() > 0)
                            <div class="space-y-4">
                                @foreach($balanceSummary as $summary)
                                    <div class="rounded-lg border border-gray-200 dark:border-gray-600 p-4 bg-gray-50 dark:bg-gray-700/50">
                                        <div class="mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $summary->leave_type->label() }}
                                        </div>
                                        <div class="grid grid-cols-3 gap-2 text-xs">
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400">Available:</span>
                                                <span class="ml-1 font-medium text-green-600 dark:text-green-400">{{ number_format($summary->total_available, 1) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400">Used:</span>
                                                <span class="ml-1 font-medium text-blue-600 dark:text-blue-400">{{ number_format($summary->total_used, 1) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400">Pending:</span>
                                                <span class="ml-1 font-medium text-yellow-600 dark:text-yellow-400">{{ number_format($summary->total_pending, 1) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">No balance data available.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Upcoming Holidays --}}
            <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Upcoming Holidays</h3>
                        <a href="{{ route('hr-admin.holidays') }}" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300">
                            View All →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @if($upcomingHolidays->count() > 0)
                        <div class="space-y-3">
                            @foreach($upcomingHolidays as $holiday)
                                <div class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-600 p-3 bg-gray-50 dark:bg-gray-700/50">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-12 w-12 flex-col items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                                            <span class="text-xs font-medium">{{ $holiday->date->format('M') }}</span>
                                            <span class="text-lg font-bold">{{ $holiday->date->format('d') }}</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $holiday->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $holiday->date->format('l, F j, Y') }}</div>
                                        </div>
                                    </div>
                                    @if($holiday->is_recurring)
                                        <span class="rounded-full bg-blue-100 dark:bg-blue-900/30 px-2 py-1 text-xs font-medium text-blue-800 dark:text-blue-300">Recurring</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming holidays.</p>
                    @endif
                </div>
            </div>

            {{-- Recent Leave Requests --}}
            <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Leave Requests</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Employee
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Dates
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Days
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Submitted
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach($recentRequests as $request)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $request->user->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $request->user->email }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $request->leave_type->label() }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $request->start_date->format('M d, Y') }} - {{ $request->end_date->format('M d, Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $request->total_days }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if($request->status === \App\Enums\LeaveStatus::Pending)
                                            <span class="inline-flex rounded-full bg-yellow-100 dark:bg-yellow-900/30 px-2 py-1 text-xs font-semibold leading-5 text-yellow-800 dark:text-yellow-300">
                                                Pending
                                            </span>
                                        @elseif($request->status === \App\Enums\LeaveStatus::Approved)
                                            <span class="inline-flex rounded-full bg-green-100 dark:bg-green-900/30 px-2 py-1 text-xs font-semibold leading-5 text-green-800 dark:text-green-300">
                                                Approved
                                            </span>
                                        @elseif($request->status === \App\Enums\LeaveStatus::Denied)
                                            <span class="inline-flex rounded-full bg-red-100 dark:bg-red-900/30 px-2 py-1 text-xs font-semibold leading-5 text-red-800 dark:text-red-300">
                                                Denied
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-1 text-xs font-semibold leading-5 text-gray-800 dark:text-gray-300">
                                                {{ $request->status->label() }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $request->submitted_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                <a href="{{ route('hr-admin.users') }}" class="block rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 bg-white dark:bg-gray-800 transition-colors">
                    <svg class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-gray-100">Manage Users</span>
                </a>

                <a href="{{ route('hr-admin.balances') }}" class="block rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 bg-white dark:bg-gray-800 transition-colors">
                    <svg class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-gray-100">Manage Balances</span>
                </a>

                <a href="{{ route('hr-admin.holidays') }}" class="block rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 bg-white dark:bg-gray-800 transition-colors">
                    <svg class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-gray-100">Manage Holidays</span>
                </a>

                <a href="{{ route('hr-admin.reports') }}" class="block rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 bg-white dark:bg-gray-800 transition-colors">
                    <svg class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-gray-100">View Reports</span>
                </a>

                <a href="{{ route('hr-admin.delegations') }}" class="block rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 bg-white dark:bg-gray-800 transition-colors">
                    <svg class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-gray-100">View Delegations</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
