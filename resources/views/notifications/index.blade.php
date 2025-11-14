<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Notifications') }}
            </h2>
            @if (auth()->user()->unreadNotifications->count() > 0)
                <form method="POST" action="{{ route('notifications.mark-all-as-read') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 dark:bg-primary-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-150">
                        Mark All as Read
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($notifications->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No notifications</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You're all caught up!</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($notifications as $notification)
                                <div class="relative flex items-start p-4 rounded-lg border {{ $notification->read_at ? 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' : 'border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-900/20' }}">
                                    <!-- Unread indicator -->
                                    @if (!$notification->read_at)
                                        <div class="flex-shrink-0 mt-1">
                                            <span class="inline-block h-2 w-2 bg-primary-600 rounded-full"></span>
                                        </div>
                                    @endif

                                    <!-- Notification content -->
                                    <div class="flex-1 {{ !$notification->read_at ? 'ml-3' : '' }}">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $notification->data['message'] ?? 'New notification' }}
                                                </p>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </p>
                                                @if (isset($notification->data['type_label']))
                                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                                        <span class="font-medium">Type:</span> {{ $notification->data['type_label'] }}
                                                    </p>
                                                @endif
                                                @if (isset($notification->data['start_date']) && isset($notification->data['end_date']))
                                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                                        <span class="font-medium">Dates:</span> {{ \Carbon\Carbon::parse($notification->data['start_date'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($notification->data['end_date'])->format('M d, Y') }}
                                                    </p>
                                                @endif
                                            </div>

                                            <!-- Actions -->
                                            <div class="flex items-center space-x-2 ml-4">
                                                @if (isset($notification->data['action_url']))
                                                    <a href="{{ $notification->data['action_url'] }}"
                                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 dark:text-primary-300 dark:bg-primary-900/50 dark:hover:bg-primary-900/70 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-150"
                                                       onclick="markAsRead('{{ $notification->id }}')">
                                                        View
                                                    </a>
                                                @endif
                                                @if (!$notification->read_at)
                                                    <form method="POST" action="{{ route('notifications.mark-as-read', $notification->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-red-500 dark:hover:text-red-400" onclick="return confirm('Are you sure you want to delete this notification?')">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function markAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
