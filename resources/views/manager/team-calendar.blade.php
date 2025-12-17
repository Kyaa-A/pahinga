<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Month Navigation -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('manager.team-calendar', ['month' => $currentMonth->copy()->subMonth()->month, 'year' => $currentMonth->copy()->subMonth()->year]) }}" class="inline-flex items-center px-5 py-3 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 font-semibold text-sm transition-colors shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </a>

                        <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $currentMonth->format('F Y') }}
                        </h3>

                        <a href="{{ route('manager.team-calendar', ['month' => $currentMonth->copy()->addMonth()->month, 'year' => $currentMonth->copy()->addMonth()->year]) }}" class="inline-flex items-center px-5 py-3 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 font-semibold text-sm transition-colors shadow-sm">
                            Next
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    <div class="mt-4 flex items-center justify-center gap-6 text-sm text-gray-700 dark:text-gray-300">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 text-primary-600 dark:text-primary-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="font-semibold">Team Size: {{ $teamSize }}</span>
                        </span>
                        <span class="text-gray-400">•</span>
                        <span>Showing approved and pending leaves</span>
                        <span class="text-gray-400">•</span>
                        <span class="flex items-center">
                            <span class="inline-block w-3 h-3 rounded bg-red-500 mr-1"></span>
                            Holiday
                        </span>
                    </div>
                </div>
            </div>

            <!-- Calendar/List View -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($leaves->isEmpty())
                        <div class="text-center py-16">
                            <div class="mx-auto h-16 w-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">No leaves scheduled</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No team members are on leave this month.</p>
                        </div>
                    @else
                        <!-- Simple Day Grid -->
                        <div class="grid grid-cols-7 gap-px bg-gray-300 dark:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                            <!-- Day Headers -->
                            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                <div class="bg-primary-50 dark:bg-primary-900/20 p-3 text-center">
                                    <span class="text-sm font-bold text-primary-800 dark:text-primary-300">{{ $day }}</span>
                                </div>
                            @endforeach

                            <!-- Calendar Days -->
                            @php
                                $startOfMonth = $currentMonth->copy()->startOfMonth();
                                $endOfMonth = $currentMonth->copy()->endOfMonth();
                                $startOfCalendar = $startOfMonth->copy()->startOfWeek();
                                $endOfCalendar = $endOfMonth->copy()->endOfWeek();
                                $currentDate = $startOfCalendar->copy();
                            @endphp

                            @while ($currentDate <= $endOfCalendar)
                                @php
                                    $isCurrentMonth = $currentDate->month === $currentMonth->month;
                                    $isToday = $currentDate->isToday();
                                    $dateKey = $currentDate->format('Y-m-d');
                                    $holiday = $holidays->get($dateKey);
                                    $leavesOnDay = $leaves->filter(function ($leave) use ($currentDate) {
                                        return $currentDate->between($leave->start_date, $leave->end_date);
                                    });
                                @endphp

                                <div class="min-h-[110px] p-3 {{ !$isCurrentMonth ? 'opacity-40' : '' }} {{ $holiday ? 'bg-red-50 dark:bg-red-900/20' : 'bg-white dark:bg-gray-800' }} hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-semibold {{ $isToday ? 'inline-flex items-center justify-center w-7 h-7 rounded-full bg-primary-600 text-white' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $currentDate->day }}
                                        </div>
                                        @if ($holiday)
                                            <span class="inline-block w-2 h-2 rounded-full bg-red-500" title="{{ $holiday->name }}"></span>
                                        @endif
                                    </div>

                                    @if ($holiday)
                                        <div class="mt-1">
                                            <div title="{{ $holiday->name }}" class="text-xs px-2 py-1 rounded-md bg-red-200 text-red-800 dark:bg-red-800 dark:text-red-200 truncate font-medium">
                                                {{ Str::limit($holiday->name, 15) }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($leavesOnDay->isNotEmpty())
                                        <div class="mt-1 space-y-1">
                                            @foreach ($leavesOnDay->take($holiday ? 2 : 3) as $leave)
                                                <div title="{{ $leave->user->name }} - {{ $leave->leave_type->label() }}"
                                                     class="text-xs px-2 py-1 rounded-md {{ $leave->status === \App\Enums\LeaveStatus::Approved ? 'bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-200' : 'bg-yellow-200 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200' }} truncate cursor-pointer hover:scale-105 transition-transform font-medium">
                                                    {{ substr($leave->user->name, 0, 12) }}{{ strlen($leave->user->name) > 12 ? '...' : '' }}
                                                </div>
                                            @endforeach
                                            @if ($leavesOnDay->count() > ($holiday ? 2 : 3))
                                                <div class="text-xs text-gray-600 dark:text-gray-400 italic font-medium px-1">
                                                    +{{ $leavesOnDay->count() - ($holiday ? 2 : 3) }} more
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                @php
                                    $currentDate->addDay();
                                @endphp
                            @endwhile
                        </div>

                        <!-- Leave Details List -->
                        <div class="mt-8">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                <svg class="h-5 w-5 mr-2 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Leave Details for {{ $currentMonth->format('F Y') }}
                            </h3>
                            <div class="space-y-3">
                                @foreach ($leaves->sortBy('start_date') as $leave)
                                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <div class="h-11 w-11 rounded-full bg-gradient-horizon flex items-center justify-center text-white font-bold">
                                                    {{ substr($leave->user->name, 0, 1) }}
                                                </div>
                                                <div class="ml-4">
                                                    <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                        {{ $leave->user->name }}
                                                    </p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                                        {{ $leave->leave_type->label() }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-6 text-right">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                                            </p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                                {{ $leave->start_date->diffInDays($leave->end_date) + 1 }} days
                                            </p>
                                        </div>
                                        <div class="ml-6">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $leave->status === \App\Enums\LeaveStatus::Approved ? 'bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-200' : 'bg-yellow-200 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200' }}">
                                                {{ $leave->status->label() }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
