<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Company-wide Reports
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View leave statistics and trends across the company</p>
            </div>

            {{-- Year Filter --}}
            <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <form method="GET" action="{{ route('hr-admin.reports') }}" class="flex items-end gap-6">
                        <div>
                            <label for="year" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Report Year</label>
                            <div class="relative">
                                <select name="year" id="year" class="w-full px-4 py-2.5 pr-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-gray-900 focus:border-primary-500 dark:focus:border-primary-500 focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-500 focus:ring-opacity-20 transition-all duration-200" style="appearance: none; background-image: none;">
                                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-horizon border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wide hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:scale-[0.98] transition-all duration-200 shadow-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Generate Report
                        </button>
                    </form>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-400">Total Requests ({{ $selectedYear }})</h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalRequests }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">All leave requests submitted</p>
                    </div>
                </div>

                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-400">Approval Rate</h3>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $approvalRate }}%</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Requests approved vs total submitted</p>
                    </div>
                </div>
            </div>

            {{-- Monthly Trend --}}
            <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Monthly Leave Trend ({{ $selectedYear }})</h3>
                </div>
                <div class="p-6">
                    @if($monthlyTrend->count() > 0)
                        <div class="space-y-4">
                            @php
                                $maxDays = $monthlyTrend->max('total_days') ?: 1;
                            @endphp
                            @foreach($monthlyTrend as $trend)
                                @php
                                    $monthName = DateTime::createFromFormat('!m', $trend->month)->format('F');
                                    $percentage = ($trend->total_days / $maxDays) * 100;
                                @endphp
                                <div>
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $monthName }}</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $trend->count }} requests ({{ number_format($trend->total_days, 1) }} days)
                                        </span>
                                    </div>
                                    <div class="h-4 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-4 rounded-full bg-gradient-horizon" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No leave data available for {{ $selectedYear }}.</p>
                    @endif
                </div>
            </div>

            {{-- Leave Type Distribution and Department Breakdown --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {{-- Leave Type Distribution --}}
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Leave Type Distribution</h3>
                    </div>
                    <div class="p-6">
                        @if($leaveTypeDistribution->count() > 0)
                            <div class="space-y-4">
                                @php
                                    $totalTypeRequests = $leaveTypeDistribution->sum('count');
                                @endphp
                                @foreach($leaveTypeDistribution as $type)
                                    @php
                                        $percentage = $totalTypeRequests > 0 ? ($type->count / $totalTypeRequests) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="mb-1 flex items-center justify-between">
                                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                {{ $type->leave_type->label() }}
                                            </span>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $type->count }} ({{ number_format($percentage, 1) }}%)
                                            </span>
                                        </div>
                                        <div class="h-3 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div class="h-3 rounded-full bg-blue-600 dark:bg-blue-500" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ number_format($type->total_days, 1) }} total days</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">No leave type data available.</p>
                        @endif
                    </div>
                </div>

                {{-- Department Breakdown --}}
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Department Breakdown</h3>
                    </div>
                    <div class="p-6">
                        @if($departmentBreakdown->count() > 0)
                            <div class="space-y-4">
                                @php
                                    $totalDeptRequests = $departmentBreakdown->sum('count');
                                @endphp
                                @foreach($departmentBreakdown as $dept)
                                    @php
                                        $percentage = $totalDeptRequests > 0 ? ($dept->count / $totalDeptRequests) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="mb-1 flex items-center justify-between">
                                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $dept->department }}</span>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $dept->count }} ({{ number_format($percentage, 1) }}%)
                                            </span>
                                        </div>
                                        <div class="h-3 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div class="h-3 rounded-full bg-purple-600 dark:bg-purple-500" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ number_format($dept->total_days, 1) }} total days</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">No department data available (departments may not be assigned).</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Info Box --}}
            <div class="rounded-xl bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 p-4">
                <h4 class="mb-2 text-sm font-semibold text-blue-900 dark:text-blue-200">About These Reports:</h4>
                <ul class="list-inside list-disc space-y-1 text-sm text-blue-800 dark:text-blue-300">
                    <li>Data shown is for approved leave requests only</li>
                    <li>Monthly trend shows when leave starts (not the entire duration)</li>
                    <li>Approval rate includes all request statuses (pending, approved, denied, cancelled)</li>
                    <li>Department breakdown only shows employees with assigned departments</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
