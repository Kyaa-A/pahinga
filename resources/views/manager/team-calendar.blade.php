<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Team Leave Calendar') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Month Navigation -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('manager.team-calendar', ['month' => $currentMonth->copy()->subMonth()->month, 'year' => $currentMonth->copy()->subMonth()->year]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </a>

                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $currentMonth->format('F Y') }}
                        </h3>

                        <a href="{{ route('manager.team-calendar', ['month' => $currentMonth->copy()->addMonth()->month, 'year' => $currentMonth->copy()->addMonth()->year]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                            Next
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    <div class="mt-4 flex items-center justify-center text-sm text-gray-600 dark:text-gray-400">
                        <span class="mr-4">Team Size: {{ $teamSize }}</span>
                        <span>Showing approved and pending leaves</span>
                    </div>
                </div>
            </div>

            <!-- Calendar/List View -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($leaves->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No leaves scheduled</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No team members are on leave this month.</p>
                        </div>
                    @else
                        <!-- Simple Day Grid -->
                        <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 border border-gray-200 dark:border-gray-700">
                            <!-- Day Headers -->
                            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                <div class="bg-gray-50 dark:bg-gray-800 p-2 text-center">
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $day }}</span>
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
                                    $leavesOnDay = $leaves->filter(function ($leave) use ($currentDate) {
                                        return $currentDate->between($leave->start_date, $leave->end_date);
                                    });
                                @endphp

                                <div class="bg-white dark:bg-gray-900 min-h-[100px] p-2 {{ !$isCurrentMonth ? 'opacity-40' : '' }}">
                                    <div class="text-sm {{ $isToday ? 'font-bold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                                        {{ $currentDate->day }}
                                    </div>

                                    @if ($leavesOnDay->isNotEmpty())
                                        <div class="mt-1 space-y-1">
                                            @foreach ($leavesOnDay->take(3) as $leave)
                                                <div title="{{ $leave->user->name }} - {{ ucwords(str_replace('_', ' ', $leave->leave_type)) }}"
                                                     class="text-xs p-1 rounded {{ $leave->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }} truncate cursor-pointer hover:scale-105 transition">
                                                    {{ substr($leave->user->name, 0, 15) }}{{ strlen($leave->user->name) > 15 ? '...' : '' }}
                                                </div>
                                            @endforeach
                                            @if ($leavesOnDay->count() > 3)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 italic">
                                                    +{{ $leavesOnDay->count() - 3 }} more
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
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Leave Details for {{ $currentMonth->format('F Y') }}</h3>
                            <div class="space-y-3">
                                @foreach ($leaves->sortBy('start_date') as $leave)
                                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                                    {{ substr($leave->user->name, 0, 1) }}
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $leave->user->name }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ ucwords(str_replace('_', ' ', $leave->leave_type)) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-4 text-right">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $leave->start_date->diffInDays($leave->end_date) + 1 }} days
                                            </p>
                                        </div>
                                        <div class="ml-4">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $leave->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                                {{ ucfirst($leave->status) }}
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
