@if(strtolower(trim(auth()->user()->role)) !== 'admin')

    @php
        $user = auth()->user();
        
        // Bulletproof filter
        $filteredNotifications = $user->customNotifications->filter(function($notification) use ($user) {
            $role = strtolower(trim($user->role));
            $type = strtolower(trim($notification->type));

            if ($role === 'teacher') {
                return $type === 'announcement'; 
            }
            return true; 
        });
    @endphp

    <!-- 1. Moved @click.away to the Parent Div -->
    <!-- 2. Added z-[999] to force it above everything -->
    <div class="relative inline-block z-[999]" x-data="{ notifOpen: false }" @click.away="notifOpen = false">
        
        <!-- BELL BUTTON -->
        <button @click="notifOpen = !notifOpen" class="relative focus:outline-none p-2 hover:scale-110 transition-transform">
            <i class="fa-solid fa-bell text-2xl text-white"></i>
            
            <!-- RED BADGE -->
            @if($filteredNotifications->count() > 0)
                <span class="absolute top-0 right-0 bg-yellow-400 text-red-700 text-[10px] rounded-full h-4 w-4 flex items-center justify-center font-bold border border-black">
                    {{ $filteredNotifications->count() }}
                </span>
            @endif
        </button>

        <!-- DROPDOWN MENU -->
        <div x-show="notifOpen" 
             x-transition.opacity.duration.200ms
             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border-[3px] border-black overflow-hidden"
             style="display: none;" 
             x-cloak>
            
            <div class="bg-gray-100 border-b-[3px] border-black px-4 py-2">
                <span class="font-black text-black uppercase text-xs tracking-widest">Notifications</span>
            </div>
            
            <div class="max-h-64 overflow-y-auto">
                @forelse($filteredNotifications as $notification)
                    <a href="{{ route('notifications.read', $notification->notification_id) }}" 
                       class="block p-4 border-b border-gray-200 hover:bg-gray-50 transition cursor-pointer no-underline">
                        
                        <div class="pointer-events-none">
                            <p class="text-[10px] font-black text-orange-600 uppercase">
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
                    <div class="p-6 text-center">
                        <p class="text-gray-500 font-bold uppercase text-xs">No Notifications Found</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

@endif