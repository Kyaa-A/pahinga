<nav x-data="{ open: false }" class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-lg border-b border-gray-200 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a wire:navigate href="{{ Auth::user()->isHRAdmin() ? route('hr-admin.dashboard') : (Auth::user()->isManager() ? route('manager.dashboard') : route('dashboard')) }}" class="flex items-center space-x-3">
                        <img src="{{ asset('pahinga.png') }}" alt="Pahinga" class="h-10 w-10">
                        <span class="text-2xl font-bold text-primary-800 dark:text-teal-400">Pahinga</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if (Auth::user()->isHRAdmin())
                        <x-nav-link :href="route('hr-admin.dashboard')" :active="request()->routeIs('hr-admin.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('hr-admin.users')" :active="request()->routeIs('hr-admin.users*')">
                            {{ __('Users') }}
                        </x-nav-link>
                        <x-nav-link :href="route('hr-admin.balances')" :active="request()->routeIs('hr-admin.balances*')">
                            {{ __('Balances') }}
                        </x-nav-link>
                        <x-nav-link :href="route('hr-admin.holidays')" :active="request()->routeIs('hr-admin.holidays*')">
                            {{ __('Holidays') }}
                        </x-nav-link>
                        <x-nav-link :href="route('hr-admin.reports')" :active="request()->routeIs('hr-admin.reports')">
                            {{ __('Reports') }}
                        </x-nav-link>
                        <x-nav-link :href="route('hr-admin.delegations')" :active="request()->routeIs('hr-admin.delegations')">
                            {{ __('Delegations') }}
                        </x-nav-link>
                    @elseif (Auth::user()->isManager())
                        <x-nav-link :href="route('manager.dashboard')" :active="request()->routeIs('manager.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('manager.pending-requests')" :active="request()->routeIs('manager.pending-requests') || request()->routeIs('manager.show-request')">
                            {{ __('Pending') }}
                        </x-nav-link>
                        <x-nav-link :href="route('manager.team-status')" :active="request()->routeIs('manager.team-status')">
                            {{ __('Team Status') }}
                        </x-nav-link>
                        <x-nav-link :href="route('manager.team-calendar')" :active="request()->routeIs('manager.team-calendar')">
                            {{ __('Calendar') }}
                        </x-nav-link>
                        <x-nav-link :href="route('manager.delegations')" :active="request()->routeIs('manager.delegations*')">
                            {{ __('Delegations') }}
                        </x-nav-link>
                        <x-nav-link :href="route('leave-requests.index')" :active="request()->routeIs('leave-requests.*')">
                            {{ __('My Requests') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('leave-requests.index')" :active="request()->routeIs('leave-requests.*')">
                            {{ __('My Leave Requests') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">
                <!-- Dark Mode Toggle -->
                <livewire:dark-mode-toggle />

                <!-- Notifications Bell (Livewire) -->
                <livewire:notification-bell />

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-5 font-semibold rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-150 shadow-sm">
                            <svg class="h-5 w-5 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-2">
                                <svg class="fill-current h-4 w-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if (Auth::user()->isHRAdmin())
                <x-responsive-nav-link :href="route('hr-admin.dashboard')" :active="request()->routeIs('hr-admin.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('hr-admin.users')" :active="request()->routeIs('hr-admin.users*')">
                    {{ __('Users') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('hr-admin.balances')" :active="request()->routeIs('hr-admin.balances*')">
                    {{ __('Balances') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('hr-admin.holidays')" :active="request()->routeIs('hr-admin.holidays*')">
                    {{ __('Holidays') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('hr-admin.reports')" :active="request()->routeIs('hr-admin.reports')">
                    {{ __('Reports') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('hr-admin.delegations')" :active="request()->routeIs('hr-admin.delegations')">
                    {{ __('Delegations') }}
                </x-responsive-nav-link>
            @elseif (Auth::user()->isManager())
                <x-responsive-nav-link :href="route('manager.dashboard')" :active="request()->routeIs('manager.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manager.pending-requests')" :active="request()->routeIs('manager.pending-requests') || request()->routeIs('manager.show-request')">
                    {{ __('Pending') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manager.team-status')" :active="request()->routeIs('manager.team-status')">
                    {{ __('Team Status') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manager.team-calendar')" :active="request()->routeIs('manager.team-calendar')">
                    {{ __('Calendar') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manager.delegations')" :active="request()->routeIs('manager.delegations*')">
                    {{ __('Delegations') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('leave-requests.index')" :active="request()->routeIs('leave-requests.*')">
                    {{ __('My Requests') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('leave-requests.index')" :active="request()->routeIs('leave-requests.*')">
                    {{ __('My Leave Requests') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
