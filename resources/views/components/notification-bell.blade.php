@if(strtolower(trim(auth()->user()->role)) !== 'admin')

    @php
        // We keep the initial load data so it doesn't flash empty when the page first loads
        $user = auth()->user();
        $initialNotifications = $user->customNotifications->filter(function($notification) use ($user) {
            $role = strtolower(trim($user->role));
            $type = strtolower(trim($notification->type));
            if ($role === 'teacher') {
                return in_array($type, ['announcement', 'event', 'school event', 'calendar', 'deadline_alert', 'appointment']); 
            }
            return true;
        })->values()->map(function($notif) {
            return [
                'notification_id' => $notif->notification_id,
                'type' => strtolower(trim($notif->type)),
                'title' => $notif->title,
                'message' => $notif->message,
                'time_ago' => $notif->created_at->diffForHumans()
            ];
        });
    @endphp

    <div class="relative inline-block z-[999]" 
         x-data="{ 
             notifOpen: false,
             notifications: {{ json_encode($initialNotifications) }},
             init() {
                 setInterval(() => {
                     fetch('{{ route('notifications.fetch') }}?t=' + Date.now())
                         .then(res => res.json())
                         .then(data => { 
                             // Normalizes data so Alpine updates the list and badge smoothly without refreshing
                             this.notifications = Array.isArray(data) ? data : Object.values(data); 
                         })
                         .catch(err => console.error('Error fetching notifications:', err));
                 }, 5000); // 5 seconds
             }
         }" 
         @click.away="notifOpen = false">
        
        <!-- BELL BUTTON -->
        <button @click="notifOpen = !notifOpen" class="relative focus:outline-none p-2 hover:scale-110 transition-transform">
            <i class="fa-solid fa-bell text-2xl text-white"></i>
            
            <!-- RED BADGE -->
            <template x-if="Object.keys(notifications).length > 0">
                <span class="absolute top-0 right-0 bg-yellow-400 text-red-700 text-[10px] rounded-full h-4 w-4 flex items-center justify-center font-bold border border-black" 
                      x-text="Object.keys(notifications).length">
                </span>
            </template>
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
                
                <template x-for="notif in notifications" :key="notif.notification_id">
                    <!-- Dynamic URL generation -->
                    <a :href="'/notifications/' + notif.notification_id + '/read'" 
                       class="block p-4 border-b border-gray-200 hover:bg-gray-50 transition cursor-pointer no-underline">
                        
                        <div>
                            <p class="text-[10px] font-black text-orange-600 uppercase">
                                <i class="fa-solid mr-1" :class="notif.type === 'deadline_alert' ? 'fa-clock text-red-600' : 'fa-circle-info'"></i>
                                <span x-text="notif.title"></span>
                            </p>
                            <p class="text-sm font-bold text-black leading-tight mt-1" x-text="notif.message"></p>
                            <p class="text-[10px] text-gray-400 mt-2" x-text="notif.time_ago"></p>
                        </div>
                    </a>
                </template>

                <template x-if="Object.keys(notifications).length === 0">
                    <div class="p-6 text-center">
                        <p class="text-gray-500 font-bold uppercase text-xs">No Notifications Found</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

@endif