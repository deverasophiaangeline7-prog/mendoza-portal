@extends('layouts.navigation')

@section('content')
<!-- Main Wrapper: Flex layout to place sidebar and chat side-by-side -->
<div class="h-[calc(100vh-100px)] w-full bg-white overflow-hidden flex" x-data="{ newMsgModal: false, createGroupModal: false }">
    
    <!-- ================== -->
    <!-- 1. CHATS SIDEBAR   -->
    <!-- ================== -->
    <div class="w-80 border-r flex flex-col bg-white flex-shrink-0">
        
        <!-- Sidebar Header -->
        <div class="p-4 font-bold text-lg border-b bg-gray-50 flex justify-between items-center relative" x-data="{ searchOpen: false, searchQuery: '' }">
            <!-- Title or Search Input container -->
            <div class="flex items-center flex-1 mr-2 relative">
                <!-- Normal Title (Shown when search is closed) -->
                <span x-show="!searchOpen" class="text-gray-800">Chats</span>

                <!-- Expanding Search Input (Shown when search is open) -->
                <div x-show="searchOpen" 
                     x-transition:enter="transition-all ease-out duration-200"
                     x-transition:enter-start="opacity-0 w-0"
                     x-transition:enter-end="opacity-100 w-full"
                     x-transition:leave="transition-all ease-in duration-150"
                     x-transition:leave-start="opacity-100 w-full"
                     x-transition:leave-end="opacity-0 w-0"
                     class="w-full flex items-center"
                     style="display: none;">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Search user name..." 
                           class="w-full text-sm border border-gray-300 rounded-full px-3 py-1.5 focus:outline-none focus:border-[#6d0101] focus:ring-1 focus:ring-[#6d0101] bg-white">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <!-- Search Button / Close Toggle -->
                <button @click="searchOpen = !searchOpen; if(searchOpen) { $nextTick(() => $el.closest('div.flex').querySelector('input').focus()) }" 
                        class="text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-full w-8 h-8 flex items-center justify-center transition" 
                        :title="searchOpen ? 'Close Search' : 'Search Account'">
                    <i class="fa-solid text-sm" :class="searchOpen ? 'fa-xmark' : 'fa-magnifying-glass'"></i>
                </button>
                
                <!-- Plus Button with Dropdown Menu -->
                <div x-data="{ dropdownOpen: false }" class="relative">
                    <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false" class="text-white bg-[#6d0101] hover:bg-red-900 rounded-full w-8 h-8 flex items-center justify-center transition">
                        <i class="fa-solid fa-plus text-sm"></i>
                    </button>

                    <!-- Dropdown Content -->
                    <div x-show="dropdownOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         style="display: none;"
                         class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden">
                        
                        <!-- Option 1: New Message -->
                        <button @click="dropdownOpen = false; newMsgModal = true" class="w-full text-left block px-4 py-3 text-sm text-gray-800 font-bold hover:bg-gray-100 border-b border-gray-100 transition-colors">
                            <i class="fa-solid fa-pen-to-square mr-2 text-[#6d0101]"></i> New Message
                        </button>
                        
                        <!-- Option 2: Create Group Chat -->
                        <button @click="dropdownOpen = false; createGroupModal = true" class="w-full text-left block px-4 py-3 text-sm text-gray-800 font-bold hover:bg-gray-100 transition-colors">
                            <i class="fa-solid fa-users mr-2 text-[#6d0101]"></i> Create a group chat
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar User List -->
        <div class="overflow-y-auto flex-1">
            @foreach($users as $user)
            <!-- Ensure everything stays inside this anchor tag -->
            <a href="{{ route('messages.show', ['id' => $user->user_id]) }}" 
               class="block p-4 border-b border-gray-300 hover:bg-gray-100 transition {{ (isset($selectedUser) && $selectedUser->user_id == $user->user_id) ? 'bg-gray-50 border-l-4 border-[#6d0101]' : 'border-l-4 border-transparent' }}">
                <div class="flex items-center">
                    
                    <!-- Avatar -->
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" class="w-12 h-12 rounded-full mr-3 border" alt="User">
                    
                    <!-- Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline">
                            <!-- Name and Section perfectly aligned -->
                            <span class="font-bold text-gray-900 truncate flex items-center gap-1">
                                @if(isset($user->type) && $user->type === 'announcement') 📌 
                                @elseif(isset($user->type) && $user->type === 'advisory') 🎓 
                                @endif    
                                <span>{{ $user->name }}</span>
                                @if($user->section_name)
                                    <span class="text-xs font-normal text-gray-500">({{ $user->section_name }})</span>
                                @endif
                            </span>

                            @php
                                $latestMsg = $user->latestMessageWithAuthUser();
                            @endphp
                            <span class="text-xs text-gray-400 ml-2 flex-shrink-0">
                                {{ $latestMsg ? $latestMsg->created_at->format('m/d') : '' }}
                            </span>
                        </div>
                        
                        <!-- Preview Text -->
                        <p class="text-sm text-gray-600 truncate mt-0.5">
                            Click to start conversation...
                        </p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    <!-- ================== -->
    <!-- 2. CHAT DETAIL VIEW-->
    <!-- ================== -->
    <div class="flex-1 flex flex-col bg-white overflow-hidden">
        
        @isset($selectedUser)
            <!-- Chat Header -->
            <div class="p-4 border-b bg-white flex items-center justify-between shadow-sm flex-shrink-0">
                <div class="flex items-center">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($selectedUser->name) }}" class="w-10 h-10 rounded-full mr-3 border" alt="User">
                    <div>
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            {{ $selectedUser->name }}
                            @if($selectedUser->section_name)
                                <span class="text-xs font-normal bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full border">
                                    {{ $selectedUser->section_name }}
                                </span>
                            @endif
                        </h3>
                        <span class="text-xs text-green-500 flex items-center">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span> Active Now
                        </span>
                    </div>
                </div>
                <div class="text-gray-400 space-x-4">
                    <i class="fa-solid fa-magnifying-glass hover:text-gray-600 cursor-pointer"></i>
                    <i class="fa-solid fa-ellipsis-vertical hover:text-gray-600 cursor-pointer"></i>
                </div>
            </div>
            
            <!-- Messages Display Area -->
            <div id="message-container" class="flex-1 overflow-y-auto p-4 space-y-4">
                @if(isset($messages) && count($messages) > 0)
                    @foreach($messages as $message)
                        <div class="{{ $message->sender_id === auth()->user()->user_id ? 'text-right' : 'text-left' }}">
                            <span class="inline-block p-3 px-4 rounded-2xl shadow-sm text-sm {{ $message->sender_id === auth()->user()->user_id ? 'bg-[#6d0101] text-white rounded-br-none' : 'bg-gray-100 text-gray-800 rounded-bl-none' }}">
                                {{ $message->content }}
                            </span>
                            <div class="text-[10px] text-gray-400 mt-1">
                                {{ $message->created_at->format('g:i A') }}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Message Input Form -->
            <div class="p-4 border-t bg-white flex-shrink-0">
                @php
                    $isAdviser = false; 
                @endphp

                @if(isset($selectedUser->type) && $selectedUser->type === 'announcement')
                    <!-- Read Only: Announcements -->
                    <div class="text-center text-sm text-gray-500 py-3 bg-gray-50 rounded-full border border-gray-200">
                        <i class="fa-solid fa-lock mr-1"></i> Only administrators can send messages in Announcements.
                    </div>
                    
                @elseif(isset($selectedUser->type) && $selectedUser->type === 'advisory' && !$isAdviser)
                    <!-- Read Only: Section -->
                    <div class="text-center text-sm text-gray-500 py-3 bg-gray-50 rounded-full border border-gray-200">
                        <i class="fa-solid fa-lock mr-1"></i> Only the adviser can send messages to this section.
                    </div>
                    
                @else
                    <!-- Normal Input Box -->
                    <form action="{{ route('messages.store') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $selectedUser->user_id }}">
                        <input type="text" name="message" class="flex-1 border border-gray-300 rounded-full px-5 py-3 focus:outline-none focus:border-[#6d0101] focus:ring-1 focus:ring-[#6d0101] transition-all" placeholder="Type your message here..." required>
                        <button type="submit" class="bg-[#6d0101] text-white px-6 py-2 rounded-full hover:bg-red-900 transition">
                            Send
                        </button>
                    </form>
                @endif
            </div>
        
        @else
            <!-- Empty State -->
            <div class="flex-1 flex flex-col items-center justify-center text-gray-400 bg-gray-50">
                <i class="fa-solid fa-comment-dots text-6xl mb-4 text-gray-300"></i>
                <p class="text-lg font-semibold text-gray-500">Click a message to view</p>
            </div>
        @endisset

    </div>

    <!-- ========================== -->
    <!-- MODAL: New Message         -->
    <!-- ========================== -->
    <div x-show="newMsgModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div @click.away="newMsgModal = false" class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="font-bold text-lg text-gray-800">New Message</h3>
                <button @click="newMsgModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <p class="text-sm text-gray-600 mb-4">Select a user from your contacts list or search for someone to start a conversation.</p>
            <div class="flex justify-end gap-2">
                <button @click="newMsgModal = false" class="px-4 py-2 bg-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-300">Cancel</button>
            </div>
        </div>
    </div>

    <!-- ========================== -->
    <!-- MODAL: Create Group Chat   -->
    <!-- ========================== -->
    <div x-show="createGroupModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div @click.away="createGroupModal = false" class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 flex flex-col max-h-[90vh]">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="font-bold text-lg text-gray-800">Create a Group Chat</h3>
                <button @click="createGroupModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <!-- Group Chat Form Setup -->
            <form action="#" method="POST" class="flex flex-col overflow-hidden h-full">
                @csrf
                
                <div class="mb-4 flex-shrink-0">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Group Name</label>
                    <input type="text" name="group_name" placeholder="Enter group name..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#6d0101]" required>
                </div>

                <div class="mb-2 flex-shrink-0">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Select Participants</label>
                </div>
                
                <!-- Scrollable Participants Checklist -->
                <div class="overflow-y-auto flex-1 border border-gray-200 rounded-lg p-2 mb-4 space-y-1 min-h-[150px]">
                    @foreach($users as $user)
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded-lg cursor-pointer transition">
                            <input type="checkbox" name="participants[]" value="{{ $user->user_id }}" class="w-4 h-4 text-[#6d0101] bg-white border-gray-300 rounded focus:ring-[#6d0101]">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" class="w-8 h-8 rounded-full ml-3 mr-3 border" alt="User">
                            <span class="text-sm font-medium text-gray-700">{{ $user->name }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-end gap-2 mt-2 flex-shrink-0 border-t pt-4">
                    <button type="button" @click="createGroupModal = false" class="px-4 py-2 bg-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-[#6d0101] text-white rounded-lg text-sm hover:bg-red-900">Create Group</button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Auto-scroll to bottom of messages -->
<script>
    const container = document.getElementById('message-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
</script>
@endsection