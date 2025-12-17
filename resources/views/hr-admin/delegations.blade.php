<x-app-layout>
    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    Manager Delegations
                </h2>
            </div>

            {{-- Filters --}}
            <div class="mb-6 overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <form method="GET" action="{{ route('hr-admin.delegations') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="manager_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Manager</label>
                            <select name="manager_id" id="manager_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <option value="">All Managers</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}" {{ request('manager_id') == $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive/Expired</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 rounded-md bg-primary-600 dark:bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 dark:hover:bg-primary-600 transition-colors">
                                Filter
                            </button>
                            <a href="{{ route('hr-admin.delegations') }}" class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Delegations Table --}}
            <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Manager
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Delegate
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Period
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Created
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @forelse($delegations as $delegation)
                                @php
                                    $isCurrentlyActive = $delegation->is_active && now()->between($delegation->start_date, $delegation->end_date);
                                    $isExpired = $delegation->end_date < now();
                                    $isFuture = $delegation->start_date > now();
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $delegation->manager->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $delegation->manager->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $delegation->delegate->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $delegation->delegate->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        <div>{{ $delegation->start_date->format('M d, Y') }}</div>
                                        <div class="text-gray-500 dark:text-gray-400">to {{ $delegation->end_date->format('M d, Y') }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if(!$delegation->is_active)
                                            <span class="inline-flex rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-1 text-xs font-semibold text-gray-800 dark:text-gray-300">Deactivated</span>
                                        @elseif($isExpired)
                                            <span class="inline-flex rounded-full bg-red-100 dark:bg-red-900/30 px-2 py-1 text-xs font-semibold text-red-800 dark:text-red-300">Expired</span>
                                        @elseif($isFuture)
                                            <span class="inline-flex rounded-full bg-blue-100 dark:bg-blue-900/30 px-2 py-1 text-xs font-semibold text-blue-800 dark:text-blue-300">Scheduled</span>
                                        @elseif($isCurrentlyActive)
                                            <span class="inline-flex rounded-full bg-green-100 dark:bg-green-900/30 px-2 py-1 text-xs font-semibold text-green-800 dark:text-green-300">Active</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $delegation->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No delegations found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($delegations->hasPages())
                    <div class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
                        {{ $delegations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
