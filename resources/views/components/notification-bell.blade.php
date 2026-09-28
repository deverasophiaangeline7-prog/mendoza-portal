@if(strtolower(trim(auth()->user()->role)) !== 'admin')

    @php
        $user = auth()->user();
        // Fetch ALL notifications directly from the Model to include read history
        $initialNotifications = \App\Models\Notification::where('user_id', $user->user_id ?? $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('notification_id') 
            ->filter(function($notification) use ($user) {
                $role = strtolower(trim($user->role));
                $type = strtolower(trim($notification->type));
                if ($role === 'teacher') {
                    return in_array($type, ['announcement', 'event', 'school event', 'calendar', 'deadline_alert', 'appointment', 'security']); 
                }
                return true;
            })->values()->map(function($notif) {
                return [
                    'notification_id' => $notif->notification_id,
                    'type' => strtolower(trim($notif->type)),
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'is_read' => (bool) $notif->is_read, // Track if it is read
                    'time_ago' => $notif->created_at ? $notif->created_at->setTimezone('Asia/Manila')->diffForHumans() : 'Just now'
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
                             this.notifications = Array.isArray(data) ? data : Object.values(data); 
                         })
                         .catch(err => console.error('Error fetching notifications:', err));
                 }, 5000); 
             },
             deleteNotification(id) {
                 this.notifications = this.notifications.filter(n => n.notification_id !== id);
                 
                 fetch('/notifications/' + id + '/delete', {
                     method: 'DELETE',
                     headers: {
                         'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                         'Content-Type': 'application/json'
                     }
                 }).catch(err => console.error('Error deleting notification:', err));
             }
         }" 
         @click.away="notifOpen = false">
        
        <!-- BELL BUTTON -->
        <button @click="notifOpen = !notifOpen" title="Notifications" class="relative focus:outline-none p-2 hover:scale-110 transition-transform">
            <i class="fa-solid fa-bell text-2xl text-white"></i>
            
            <!-- RED BADGE: Only show count for UNREAD notifications -->
            <template x-if="notifications.filter(n => !n.is_read).length > 0">
                <span class="absolute top-0 right-0 bg-yellow-400 text-red-700 text-[10px] rounded-full h-4 w-4 flex items-center justify-center font-bold border border-black" 
                      x-text="notifications.filter(n => !n.is_read).length">
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
                    <!-- Apply slight transparency if notification is already read -->
                    <div class="relative border-b border-gray-200 transition group" :class="notif.is_read ? 'bg-white opacity-80' : 'bg-gray-50'">
                        
                        <a :href="notif.type === 'appointment' ? '/notifications/' + notif.notification_id + '/read?redirect_to=' + encodeURIComponent('/appointments?action=view_requests') : '/notifications/' + notif.notification_id + '/read'" class="block p-4 pr-12 cursor-pointer no-underline">
                            <div>
                                <p class="text-[10px] font-black uppercase" :class="notif.is_read ? 'text-gray-500' : 'text-orange-600'">
                                    <i class="fa-solid mr-1" :class="notif.type === 'deadline_alert' ? 'fa-clock text-red-600' : 'fa-circle-info'"></i>
                                    <span x-text="notif.title"></span>
                                </p>
                                <p class="text-sm font-bold text-black leading-tight mt-1" x-text="notif.message"></p>
                                <p class="text-[10px] mt-2" :class="notif.is_read ? 'text-gray-400' : 'text-gray-500 font-semibold'" x-text="notif.time_ago"></p>
                            </div>
                        </a>

                        <!-- "X" Close Button instead of Trash -->
                        <button @click.stop="deleteNotification(notif.notification_id)" 
                                class="absolute top-4 right-4 text-gray-400 hover:text-red-600 transition" 
                                title="Close Notification">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                        
                    </div>
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