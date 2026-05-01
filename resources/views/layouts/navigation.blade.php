<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Right Side: Bell and Settings -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                
                <!-- 🔔 NOTIFICATION BELL WIDGET START -->
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen" @click.away="notifOpen = false" class="text-gray-500 hover:text-gray-700 transition relative p-2 focus:outline-none">
                        <i class="fa-solid fa-bell text-xl"></i>
                        
                        <!-- Red/Yellow Badge -->
                        @if(auth()->user()->customNotifications && auth()->user()->customNotifications->count() > 0)
                            <span class="absolute top-1 right-1 bg-yellow-400 text-red-700 text-[10px] rounded-full h-4 w-4 flex items-center justify-center border border-white font-bold shadow-sm">
                                {{ auth()->user()->customNotifications->count() }}
                            </span>
                        @endif
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="notifOpen" 
                         x-transition 
                         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] border-[3px] border-black py-0 z-50 overflow-hidden" 
                         style="display: none;" 
                         x-cloak>
                        
                        <div class="bg-gray-200 border-b-[3px] border-black px-4 py-3">
                            <h3 class="font-black uppercase tracking-wider text-black text-xs">Notifications</h3>
                        </div>

                        <div class="max-h-80 overflow-y-auto">
                            @forelse(auth()->user()->customNotifications as $notification)
                                <!-- FIXED: Pointing to markRead route instead of hardcoding Report Card -->
                                <a href="{{ route('notifications.markRead', $notification->notification_id) }}" 
                                   class="block w-full p-4 border-b-2 border-gray-200 hover:bg-gray-50 transition cursor-pointer relative z-[110] no-underline text-left">
                                   
                                   <div class="pointer-events-none">
                                       <p class="text-[10px] font-black text-orange-600 uppercase mb-1">
                                           {{ $notification->title }}
                                       </p>
                                       
                                       <p class="text-sm font-bold text-black leading-tight">
                                           {{ $notification->message }}
                                       </p>
                                       
                                       <p class="text-[10px] text-gray-400 mt-2">
                                           {{ $notification->created_at->diffForHumans() }}
                                       </p>
                                   </div>
                                </a>
                            @empty
                                <div class="px-4 py-8 text-center">
                                    <p class="text-gray-500 font-bold uppercase text-xs">No notifications found</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <!-- 🔔 NOTIFICATION BELL WIDGET END -->

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
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

            <!-- Hamburger (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
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
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
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