<div class="relative" x-data="{ open: false }">
    <!-- The Button (Always Visible) -->
    <button @click="open = !open" 
            class="relative hover:scale-110 transition-transform focus:outline-none flex items-center">
        <i class="fa-solid fa-envelope text-white"></i>
        
        <!-- Notification Badge -->
        <span class="absolute -top-2 -right-2 bg-yellow-400 text-red-700 text-xs rounded-full h-5 w-5 flex items-center justify-center border border-red-700 font-bold">
            1
        </span>
    </button>

    <!-- The Dropdown Menu (Hidden by default) -->
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute right-0 mt-3 w-80 bg-white rounded-lg shadow-2xl z-[60] border border-gray-200 overflow-hidden"
         style="display: none;"
         x-cloak>
         
        <div class="p-3 border-b bg-gray-50 text-gray-700 font-bold">
            Chats
        </div>

        <div class="max-h-96 overflow-y-auto">
            <!-- 1. School Wide Announcements -->
            <a href="#" class="flex items-center px-4 py-3 hover:bg-gray-50 border-b border-gray-100 transition-colors">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3 text-lg">📌</div>
                <div class="flex-1 overflow-hidden">
                    <p class="font-bold text-sm text-gray-800">Announcements</p>
                    <p class="text-xs text-gray-500 truncate">No classes on March 20, 2026...</p>
                </div>
            </a>

            <!-- 2. Advisory Class -->
            <a href="#" class="flex items-center px-4 py-3 hover:bg-gray-50 border-b border-gray-100 transition-colors">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3 text-lg">🏫</div>
                <div class="flex-1 overflow-hidden">
                    <p class="font-bold text-sm text-gray-800">Room Announcements</p>
                    <p class="text-xs text-gray-500 truncate">PTC Meeting: March...</p>
                </div>
            </a>

            <!-- 3. Private Message -->
            <a href="#" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                <img src="https://ui-avatars.com/api/?name=Sophia+De+Vera" class="w-10 h-10 rounded-full mr-3" alt="User">
                <div class="flex-1 overflow-hidden">
                    <p class="font-bold text-sm text-gray-800">Sophia De Vera</p>
                    <p class="text-xs text-gray-500 truncate">You: This is noted...</p>
                </div>
            </a>
        </div>

        <div class="p-2 border-t text-center">
             <a href="{{ route('messages.index') }}" class="text-xs text-red-700 font-bold hover:underline">
             View all in Chat System
            </a>
        </div>
    </div>
</div>