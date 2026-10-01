@extends('layouts.navigation')

@section('title', 'Messages - Mendoza Academy')

@section('content')
@if(request()->query('deleted') == 1)
    <div id="brutal-toast" style="position: fixed; bottom: 40px; right: 40px; z-index: 99999; background-color: #43e276; color: black; border: 4px solid black; border-radius: 12px; box-shadow: 8px 8px 0px 0px black; padding: 16px 28px; font-family: system-ui, sans-serif; font-weight: 900; font-size: 16px; text-transform: uppercase; display: flex; align-items: center; gap: 12px; letter-spacing: 0.5px; transition: opacity 0.4s ease;">
        <i class="fa-solid fa-circle-check" style="font-size: 22px;"></i>
        <span>Group Deleted!</span>
    </div>
    
    <script>
        setTimeout(() => {
            let toast = document.getElementById('brutal-toast');
            if (toast) {
                toast.style.opacity = '0';
                setTimeout(() => { toast.style.display = 'none'; }, 400); 
            }
        }, 4000);

        
        if(window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
@endif
<style>
    .typing-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 12px 16px;
        background-color: #f3f4f6;
        border-radius: 1rem;
        border-bottom-left-radius: 0;
        width: fit-content;
    }
    .typing-indicator span {
        width: 8px;
        height: 8px;
        background-color: #9ca3af;
        border-radius: 50%;
        animation: wave 1.4s infinite ease-in-out both;
    }
    .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
    .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }
    
    @keyframes wave {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }
</style>

<div class="h-[calc(100vh-100px)] w-full bg-white overflow-hidden flex" x-data="chatSystem()">
    
    <!-- Sidebar / Chat List Container -->
    <div class="{{ isset($selectedUser) ? 'hidden md:flex' : 'flex w-full' }} md:w-80 border-r flex-col bg-white flex-shrink-0">
        <div class="p-4 font-bold text-lg border-b bg-gray-50 flex justify-between items-center relative">
            <div class="flex items-center flex-1 mr-2 relative">
                <span x-show="!searchOpen" class="text-gray-800">Chats</span>
                <div x-show="searchOpen" class="w-full flex items-center" style="display: none;">
                    <input type="text" x-model="searchQuery" placeholder="Search user name..." class="w-full text-sm border border-gray-300 rounded-full px-3 py-1.5 focus:outline-none focus:border-[#6d0101] focus:ring-1 focus:ring-[#6d0101] bg-white">
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <button @click="searchOpen = !searchOpen" title="Search" class="text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-full w-8 h-8 flex items-center justify-center transition">
                    <i class="fa-solid text-sm" :class="searchOpen ? 'fa-xmark' : 'fa-magnifying-glass'"></i>
                </button>
                
                <div x-data="{ dropdownOpen: false }" class="relative">
                    <button title="New Message" @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false" class="text-white bg-[#6d0101] hover:bg-red-900 rounded-full w-8 h-8 flex items-center justify-center transition">
                        <i class="fa-solid fa-plus text-sm"></i>
                    </button>

                    <div x-show="dropdownOpen" style="display: none;" class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden">
                        <button  @click="dropdownOpen = false; newMsgModal = true" class="w-full text-left block px-4 py-3 text-sm text-gray-800 font-bold hover:bg-gray-100 border-b border-gray-100 transition-colors">
                            <i class="fa-solid fa-pen-to-square mr-2 text-[#6d0101]"></i> New Message
                        </button>
                        @if(auth()->user()->role !== 'parent')
                        <button @click="dropdownOpen = false; createGroupModal = true" class="w-full text-left block px-4 py-3 text-sm text-gray-800 font-bold hover:bg-gray-100 transition-colors">
                            <i class="fa-solid fa-users mr-2 text-[#6d0101]"></i> Create a group chat
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="overflow-y-auto flex-1">
            @forelse($users as $user)
                @php
                    $hasUnread = $user->unreadMessagesCount() > 0;
                    $latestMsg = $user->latestMessageWithAuthUser();
                @endphp
                <a href="{{ route('messages.show', ['id' => $user->user_id]) }}" 
                    x-show="searchQuery === '' || '{{ strtolower(addslashes($user->custom_name ?? $user->name)) }}'.includes(searchQuery.toLowerCase())"
                    class="block p-4 border-b border-gray-200 transition {{ $hasUnread ? 'bg-blue-50/70' : 'bg-white hover:bg-gray-50' }} {{ (isset($selectedUser) && $selectedUser->user_id == $user->user_id) ? 'border-l-4 border-[#6d0101] bg-gray-50' : 'border-l-4 border-transparent' }}">
                    <div class="flex items-center">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" class="w-12 h-12 rounded-full mr-3 border flex-shrink-0" alt="User">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline">
                                <span class="truncate flex items-center gap-1 min-w-0 {{ $hasUnread ? 'font-bold text-black' : 'font-bold text-gray-900' }}">
                                    @if(isset($user->type) && $user->type === 'announcement') 📌 
                                    @elseif(isset($user->type) && $user->type === 'advisory') 🎓 
                                    @endif    
                                    <span class="truncate">{{ $user->custom_name ?? $user->name }}</span>
                                    @if($hasUnread)
                                        <span class="bg-red-600 text-white rounded-full px-2 py-0.5 text-[10px] font-bold ml-1 flex-shrink-0">{{ $user->unreadMessagesCount() }}</span>
                                    @endif
                                </span>
                            </div>
                            <!-- This is the updated, constrained paragraph -->
                            <p id="sidebar-text-{{ $user->user_id }}" class="text-xs block mt-0.5 {{ $hasUnread ? 'text-gray-900 font-semibold' : 'text-gray-500' }}" style="display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; max-height: 1.15em; line-height: 1.15em;">
                                {{ $latestMsg ? $latestMsg->content : 'No messages yet...' }}
                            </p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-gray-500 text-sm">No active chats. Click the + icon to start a new conversation.</div>
            @endforelse
            
            <!-- Archived Groups Dropdown -->
            @if(isset($archivedGroups) && $archivedGroups->count() > 0)
            <div x-data="{ showArchived: false }" class="border-t border-gray-200 mt-auto">
                <button @click="showArchived = !showArchived" class="w-full p-4 flex justify-between items-center bg-gray-50 hover:bg-gray-100 text-sm font-bold text-gray-600 transition">
                    <span><i class="fa-solid fa-box-archive mr-2"></i> Archived Groups ({{ $archivedGroups->count() }})</span>
                    <i class="fa-solid" :class="showArchived ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
                <div x-show="showArchived" style="display: none;" class="bg-gray-50 border-t border-gray-200">
                    @foreach($archivedGroups as $archived)
                        <a href="{{ route('messages.show', ['id' => $archived->user_id]) }}" class="block p-3 border-b border-gray-200 hover:bg-orange-50 transition {{ (isset($selectedUser) && $selectedUser->user_id == $archived->user_id) ? 'border-l-4 border-orange-500 bg-orange-50' : 'border-l-4 border-transparent' }}">
                            <div class="flex items-center opacity-75">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($archived->custom_name) }}" class="w-10 h-10 rounded-full mr-3 border grayscale" alt="User">
                                <div class="flex-1 min-w-0">
                                    <span class="truncate font-bold text-gray-700 text-sm">{{ $archived->custom_name }}</span>
                                    <p class="text-[10px] text-orange-600 font-bold uppercase mt-0.5">Archived</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Main Chat Area Container -->
    <div class="{{ isset($selectedUser) ? 'flex' : 'hidden md:flex' }} flex-1 flex-col bg-white overflow-hidden">
        @isset($selectedUser)
            <div class="p-4 border-b bg-white flex items-center justify-between shadow-sm flex-shrink-0 w-full">
                <div class="flex items-center">
                    <!-- Mobile Back Button -->
                    <a href="javascript:history.back()" class="md:hidden mr-3 text-gray-500 hover:text-gray-800">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>

                    <img src="https://ui-avatars.com/api/?name={{ urlencode($selectedUser->custom_name ?? $selectedUser->name) }}" class="w-10 h-10 rounded-full mr-3 border" alt="User">
                    <div>
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            {{ $selectedUser->custom_name ?? $selectedUser->name }}
                        </h3>
                        @if(isset($isGroupArchived) && $isGroupArchived)
                            <span class="text-xs text-orange-500 flex items-center mt-0.5"><span class="w-2 h-2 bg-orange-500 rounded-full mr-1"></span> Archived Group</span>
                        @elseif($selectedUser->isOnline())
                            <span class="text-xs text-green-500 flex items-center mt-0.5"><span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span> Active Now</span>
                        @else
                            <span class="text-xs text-gray-400 flex items-center mt-0.5"><span class="w-2 h-2 bg-gray-400 rounded-full mr-1"></span> Offline</span>
                        @endif
                    </div>
                </div>

                <!-- Group Chat Options Menu -->
                @if(!is_null($selectedUser->custom_name))
                <div x-data="{ groupMenuOpen: false }" class="relative ml-auto">
                    <button @click="groupMenuOpen = !groupMenuOpen" @click.away="groupMenuOpen = false" class="text-gray-500 hover:bg-gray-100 p-2 rounded-full transition w-10 h-10 flex items-center justify-center">
                        <i class="fa-solid fa-ellipsis-vertical text-xl"></i>
                    </button>
                    
                    <div x-show="groupMenuOpen" style="display: none;" class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden">
                        
                        <!-- Archive Group -->
                        @if(!isset($isGroupArchived) || !$isGroupArchived)
                        <form action="{{ route('messages.group.archive', $selectedUser->user_id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm text-gray-800 font-bold hover:bg-orange-50 transition-colors">
                                <i class="fa-solid fa-box-archive mr-2 text-orange-600"></i> Archive Group
                            </button>
                        </form>
                        @endif

                        <!-- Restore Group -->
                        @if(isset($isGroupArchived) && $isGroupArchived)
                        <form action="{{ route('messages.group.restore', $selectedUser->user_id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm text-gray-800 font-bold hover:bg-green-50 transition-colors border-b border-gray-100">
                                <i class="fa-solid fa-clock-rotate-left mr-2 text-green-600"></i> Restore Group
                            </button>
                        </form>
                        @endif

                        <!-- Delete Group -->
                        <button @click="groupMenuOpen = false; deleteModal = true" type="button" class="w-full text-left px-4 py-3 text-sm text-gray-800 font-bold hover:bg-red-50 transition-colors border-t border-gray-100">
                            <i class="fa-solid fa-trash mr-2 text-red-600"></i> Delete Group
                        </button>
                        
                    </div>
                </div>
                @endif
            </div>
            
            <div id="message-container" class="flex-1 overflow-y-auto p-4 flex flex-col">
                
                <!-- Dedicated chat messages wrapper -->
                <div id="chat-messages" class="space-y-4 flex-1">
                    @if(isset($messages) && count($messages) > 0)
                        @foreach($messages as $message)
                            @if(str_contains($message->content, 'created the group:'))
                                <!-- System Alert Styling -->
                                <div class="text-center my-4 w-full">
                                    <span class="inline-block px-4 py-1.5 bg-gray-100 text-gray-500 text-xs font-semibold rounded-full border border-gray-200 shadow-sm">
                                        @if($message->sender_id == auth()->user()->user_id)
                                            <!-- If the logged-in user is the one who created it -->
                                            You created the group: {{ trim(explode('created the group:', $message->content)[1] ?? '') }}
                                        @else
                                            <!-- If someone else created it -->
                                            {{ $message->content }}
                                        @endif
                                    </span>
                                </div>
                            @else
                                <!-- Normal Chat Bubble Styling -->
                                <div class="mb-4 w-full {{ $message->sender_id === auth()->user()->user_id ? 'text-right' : 'text-left' }}">
                                    <span class="inline-block p-3 px-4 rounded-2xl shadow-sm text-sm {{ $message->sender_id === auth()->user()->user_id ? 'bg-[#6d0101] text-white rounded-br-none' : 'bg-gray-100 text-gray-800 rounded-bl-none' }}">
                                        {{ $message->content }}
                                    </span>
                                    
                                    <!-- Added 'js-sent-msg-seen-container' class -->
                                    <div class="text-[10px] text-gray-400 mt-1 {{ $message->sender_id === auth()->user()->user_id ? 'js-sent-msg-seen-container' : '' }}">
                                        {{ $message->created_at->format('g:i A') }}
                                        
                                        <!-- Added 'js-realtime-seen-text' span wrapper -->
                                        <span class="js-realtime-seen-text">
                                            @if($message->sender_id === auth()->user()->user_id && $message->is_read)
                                                <span class="font-bold ml-1 text-gray-500">· Seen</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <!-- Added ID here (EMPTY STATE IS SAFE HERE) -->
                        <div id="empty-chat-state" class="h-full flex flex-col items-center justify-center text-gray-400">
                            <p class="text-sm">No messages yet. Send a message to start the conversation.</p>
                        </div>
                    @endif
                </div>
                
                <!-- Live Typing Bubble -->
                <div class="text-left mt-4 flex-shrink-0" x-show="otherUserIsTyping" x-cloak>
                    <div class="typing-indicator shadow-sm">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t bg-white flex-shrink-0">
                @php $isAdviser = false; @endphp
                @if(isset($isGroupArchived) &&$isGroupArchived)
                    <div class="text-center text-sm text-orange-600 font-bold py-3 bg-orange-50 rounded-full border border-orange-200">
                        <i class="fa-solid fa-box-archive mr-1"></i> This group is archived. You cannot send new messages.
                    </div>
                @elseif(isset($selectedUser->type) &&$selectedUser->type === 'announcement')
                    <div class="text-center text-sm text-gray-500 py-3 bg-gray-50 rounded-full border border-gray-200">
                        <i class="fa-solid fa-lock mr-1"></i> Only administrators can send messages.
                    </div>
                @else
                    <form @submit.prevent="sendMessage('{{ $selectedUser->user_id }}')" class="flex gap-2">
                        <input type="text" 
                               x-model="messageInput" 
                               @input="sendTypingWhisper()"
                               :disabled="thisUserIsTyping || isSending"
                               class="flex-1 border border-gray-300 rounded-full px-5 py-3 focus:outline-none focus:border-[#6d0101] focus:ring-1 focus:ring-[#6d0101] transition-all disabled:opacity-50" 
                               placeholder="Type your message here..." 
                               required>
                        <button type="submit" :disabled="thisUserIsTyping || isSending" x-text="isSending ? 'Sending...' : 'Send'" class="bg-[#6d0101] text-white px-6 py-2 rounded-full hover:bg-red-900 transition disabled:opacity-50">
                            Send
                        </button>
                    </form>
                @endif
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-gray-400 bg-gray-50">
                <i class="fa-solid fa-comment-dots text-6xl mb-4 text-gray-300"></i>
                <p class="text-lg font-semibold text-gray-500">Click a message to view</p>
            </div>
        @endisset
    </div>

    <!-- New Message Modal -->
    <div x-show="newMsgModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden flex flex-col" @click.away="newMsgModal = false">
            <div class="p-4 border-b bg-[#6d0101] text-white flex justify-between items-center">
                <h3 class="font-bold text-lg">New Message</h3>
                <button @click="newMsgModal = false" class="hover:text-gray-300 transition text-xl">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <div class="p-6" x-data="{ userSearch: '' }">
                
                <!-- Search Bar -->
                <div class="relative mb-4">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
                    <input type="text" x-model="userSearch" placeholder="Search by name or role..." class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#6d0101] transition-colors">
                </div>

                <!-- Scrollable Contact List -->
                <div class="max-h-60 overflow-y-auto border-2 border-gray-100 rounded-xl divide-y divide-gray-100">
                    @if(isset($contacts) && count($contacts) > 0)
                        @foreach($contacts as $contact)
                            <a href="{{ route('messages.show', $contact->user_id) }}" 
                               class="flex items-center p-3 hover:bg-red-50 transition-colors"
                               x-show="userSearch === '' || '{{ strtolower(addslashes($contact->name . ' ' .$contact->role)) }}'.includes(userSearch.toLowerCase())">
                                
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($contact->name) }}" class="w-10 h-10 rounded-full mr-3 border" alt="User">
                                
                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm leading-tight">{{ $contact->name }}</h4>
                                    <span class="text-[10px] font-black uppercase tracking-wider {{ $contact->role === 'admin' ? 'text-blue-600' : ($contact->role === 'teacher' ? 'text-amber-600' : 'text-gray-500') }}">
                                        {{ $contact->role }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    @else
                        <div class="p-6 text-center text-gray-500 text-sm font-bold">
                            No contacts available.
                        </div>
                    @endif
                </div>

                <div class="flex justify-end mt-4">
                    <button @click="newMsgModal = false" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-xl font-bold hover:bg-gray-300 transition">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Group Chat Modal -->
    <div x-show="createGroupModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden flex flex-col" @click.away="createGroupModal = false">
            <div class="p-4 border-b bg-[#6d0101] text-white flex justify-between items-center">
                <h3 class="font-bold text-lg">Create Group Chat</h3>
                <button @click="createGroupModal = false" class="hover:text-gray-300 transition text-xl">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <div class="p-6" x-data="{ groupName: '', groupSearch: '', selectedMembers: [] }">
                <!-- NOTE: Update the action to point to your backend route when you build it -->
                <form action="{{ route('messages.group.store') }}" method="POST">
                    @csrf
                    
                    <!-- Group Name Input -->
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Group Name <span class="text-red-600">*</span></label>
                        <input type="text" name="group_name" x-model="groupName" placeholder="E.g., Grade 1 Parents" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#6d0101] transition-colors" required>
                    </div>

                    <!-- Search Contacts -->
                    <div class="relative mb-3">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="text" x-model="groupSearch" placeholder="Search members to add..." class="w-full pl-11 pr-4 py-2.5 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#6d0101] transition-colors text-sm">
                    </div>

                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Select Members</label>
                    
                    <!-- Scrollable Contact List -->
                    <div class="max-h-52 overflow-y-auto border-2 border-gray-100 rounded-xl divide-y divide-gray-100 mb-6 shadow-inner bg-gray-50">
                        @if(isset($contacts) && count($contacts) > 0)
                            @foreach ($contacts as $contact)
                                <label class="flex items-center p-3 hover:bg-red-50 cursor-pointer transition-colors bg-white"
                                        x-show="groupSearch === '' || '{{ strtolower(addslashes($contact->name . ' ' .$contact->role)) }}'.includes(groupSearch.toLowerCase())">
                                    
                                    <input type="checkbox" name="members[]" value="{{ $contact->user_id }}" x-model="selectedMembers" class="rounded text-[#6d0101] border-gray-300 focus:ring-[#6d0101] w-4 h-4 mr-3">
                                    
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($contact->name) }}" class="w-9 h-9 rounded-full mr-3 border" alt="User">
                                    
                                    <div>
                                        <h4 class="font-bold text-gray-800 text-sm leading-tight">{{ $contact->name }}</h4>
                                        <span class="text-[10px] font-black uppercase tracking-wider {{ $contact->role === 'admin' ? 'text-blue-600' : ($contact->role === 'teacher' ? 'text-amber-600' : 'text-gray-500') }}">
                                            {{ $contact->role }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        @else
                            <div class="p-6 text-center text-gray-500 text-sm font-bold">No contacts available.</div>
                        @endif
                    </div>
                    

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 border-t pt-4">
                        <button type="button" @click="createGroupModal = false" class="bg-gray-200 text-gray-800 px-5 py-2.5 rounded-xl font-bold hover:bg-gray-300 transition">Cancel</button>
                        
                        <button type="submit" 
                                :disabled="groupName.trim() === '' || selectedMembers.length === 0" 
                                class="bg-[#6d0101] text-white px-5 py-2.5 rounded-xl font-bold hover:bg-red-900 transition disabled:opacity-50 flex items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-users"></i> Create Group
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal (Now safely outside!) -->
    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-xl w-full max-sm overflow-hidden flex flex-col p-6 text-center" @click.away="deleteModal = false">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4 text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="font-bold text-lg text-gray-800 mb-2">Delete Group Chat?</h3>
            <p class="text-sm text-gray-500 mb-6">Are you sure you want to permanently delete this group and all its messages? This cannot be undone.</p>
            
            <div class="flex justify-center gap-3">
                <button @click="deleteModal = false" type="button" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2.5 rounded-xl font-bold hover:bg-gray-200 transition">Cancel</button>
                
                @isset($selectedUser)
                <form action="{{ route('messages.group.delete', $selectedUser->user_id ?? 0) }}" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2.5 rounded-xl font-bold hover:bg-red-700 transition shadow-sm">Delete</button>
                </form>
                @endisset
            </div>
        </div>
    </div>

</div>

<!-- CRITICAL: Refreshed Script for Perfect Real-Time Sync & Seen -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('chatSystem', () => ({
            searchOpen: false,
            searchQuery: '',
            newMsgModal: false,
            createGroupModal: false,
            deleteModal: false,
            messageInput: '',
            thisUserIsTyping: false, 
            otherUserIsTyping: false, 
            isSending: false,
            myId: '{{ auth()->user()->user_id }}',
            selectedUserId: '{{ isset($selectedUser) ?$selectedUser->user_id : "" }}',
            selectedUserName: '{{ isset($selectedUser) ? (isset($selectedUser->custom_name) ? $selectedUser->custom_name :$selectedUser->name) : "" }}',
            isGroupChat: '{{ is_null($selectedUser->custom_name ?? null) ? "false" : "true" }}' === 'true',
            
            otherUserTypingTimer: null,
            pollingInterval: null, // Timer reference for the polling loop

            init() {
                const container = document.getElementById('message-container');
                // 1. Instant scroll to bottom on load
                if (container) container.scrollTop = container.scrollHeight;

                if (this.selectedUserId && this.myId) {
                    // ==========================================
                    // 1. CORE SYNC & SEEN WIRING (HYBRID LOOP)
                    // ==========================================
                    
                    // --- DB SEEN INITIALIZER ---
                    // On initial load, tell the DB I've seen their messages in this 1-on-1 chat.
                    this.dynamicallyMarkRead();

                    // --- WebSocket Seen Whisper (UI-only Seen Text) ---
                    if (window.Echo) {
                        // Tell them I opened this chat (adds dynamic seen text to their open screen instantly)
                        window.Echo.private(`chat.${this.selectedUserId}`).whisper('read', { senderId: this.myId });

                        // Listen for when THEY open my chat. Dynamically add dynamic 'Seen' text to MY open screen.
                        window.Echo.private(`chat.${this.myId}`).listenForWhisper('read', (e) => {
                            if (e.senderId == this.selectedUserId) {
                                // Find ALL my currently unread status containers on screen and mark seen.
                                const unreadStatusSpans = document.querySelectorAll('.js-sent-msg-seen-container .js-realtime-seen-text');
                                unreadStatusSpans.forEach(statusSpan => {
                                    if (statusSpan.innerHTML.trim() === '') {
                                        statusSpan.innerHTML = '<span class="font-bold ml-1 text-gray-500">· Seen</span>';
                                    }
                                });
                            }
                        });
                    }

                    // --- THE POLLING LOOP (Heartbeat of Sync) ---
                    // Run this loop every 4 seconds while this conversation is open.
                    this.pollingInterval = setInterval(() => {
                        this.pollNewMessages();
                    }, 4000); 

                }

                if (window.Echo && this.myId) {
                    // ==========================================
                    // 2. TYPING INDICATOR LOGIC (RECEIVE ONLY)
                    // ==========================================
                    window.Echo.private(`chat.${this.myId}`)
                        .listenForWhisper('typing', (e) => {
                            if (e.senderId == this.selectedUserId) {
                                this.otherUserIsTyping = true;
                                clearTimeout(this.otherUserTypingTimer);
                                this.otherUserTypingTimer = setTimeout(() => {
                                    this.otherUserIsTyping = false;
                                }, 2000);
                            }
                        });

                    // Ensure your backend broadcasts 'RealtimeSidebarUpdate' when saving messages.
                    window.Echo.private(`sidebar.${this.myId}`)
                        .listen('RealtimeSidebarUpdate', (e) => {
                            // The backend payload 'e' should include senderId, messageContent, and time.
                            const sidebarMessageElement = document.getElementById(`latest-msg-text-${e.senderId}`);
                            const sidebarContainer = document.getElementById(`user-sidebar-container-${e.senderId}`);
                            
                            if (sidebarMessageElement && sidebarContainer) {
                                // 1. Real-time update the message preview text
                                sidebarMessageElement.innerText = e.messageContent;

                                // 2. Add unread visual cues to the entire sidebar item (e.g., set blue-50 and bold).
                                sidebarContainer.classList.add('bg-blue-50/70', 'font-bold', 'text-black');
                                sidebarContainer.classList.remove('bg-white', 'hover:bg-gray-50');

                                // 3. Also update the message text styling to be unread
                                sidebarMessageElement.classList.add('text-gray-900', 'font-semibold');
                                sidebarMessageElement.classList.remove('text-gray-500');
                            }
                        });

                }
            },

            // ==========================================
            // 3. CORE SYNCING METHODS
            // ==========================================

            /**
             * Polling Loop Logic: Fetches new unread messages Dynamically.
             */
            /**
             * Polling Loop Logic: Fetches new unread messages AND checks "Seen" status.
             */
            async pollNewMessages() {
                if (!this.selectedUserId) return;
                
                try {
                    // Call the backend poll endpoint
                    const response = await fetch(`/messages/${this.selectedUserId}/poll`);
                    if (!response.ok) return;
                    
                    const data = await response.json();
                    const newMessagesData = data.messages || [];
                    
                    // 👇 NEW: Update the DOM if the other person read our messages
                    if (data.last_sent_read) {
                        const unreadStatusSpans = document.querySelectorAll('.js-sent-msg-seen-container .js-realtime-seen-text');
                        unreadStatusSpans.forEach(statusSpan => {
                            if (statusSpan.innerHTML.trim() === '') {
                                statusSpan.innerHTML = '<span class="font-bold ml-1 text-gray-500">· Seen</span>';
                            }
                        });
                    }

                    if (newMessagesData.length > 0) {
                        const chatMessagesWrapper = document.getElementById('chat-messages');
                        
                        const emptyState = document.getElementById('empty-chat-state');
                        if (emptyState) emptyState.remove();

                        let needsMarkRead = false;

                        newMessagesData.forEach(msg => {
                            const existingMsgBubble = document.getElementById(`msg-bubble-${msg.id}`);
                            if (!existingMsgBubble) {
                                needsMarkRead = true; // DB seen needed

                                chatMessagesWrapper.insertAdjacentHTML('beforeend', `
                                    <div id="msg-bubble-${msg.id}" class="mb-4 w-full text-left js-dynamically-fetched-bubble">
                                        <span class="inline-block p-3 px-4 rounded-2xl shadow-sm text-sm bg-gray-100 text-gray-800 rounded-bl-none">
                                            ${msg.content}
                                        </span>
                                        <div class="text-[10px] text-gray-400 mt-1">
                                            ${msg.time_formatted}
                                        </div>
                                    </div>
                                `);
                            }
                        });

                        const sidebarText = document.getElementById(`sidebar-text-${this.selectedUserId}`);
                        const sidebarItem = document.getElementById(`sidebar-item-${this.selectedUserId}`);
                        if (sidebarText && newMessagesData.length > 0) {
                            sidebarText.innerText = newMessagesData[newMessagesData.length - 1].content;
                            sidebarText.classList.remove('text-gray-500');
                            sidebarText.classList.add('text-gray-900', 'font-semibold');
                        }
                        if (sidebarItem && needsMarkRead && !this.isGroupChat) {
                             sidebarItem.classList.add('bg-blue-50/70');
                             sidebarItem.classList.remove('bg-white', 'hover:bg-gray-50');
                        }

                        this.$nextTick(() => { 
                            const container = document.getElementById('message-container');
                            if(container) container.scrollTop = container.scrollHeight; 
                        });

                        if (needsMarkRead && !this.isGroupChat) {
                            this.dynamicallyMarkRead();
                        }
                    }
                    
                } catch (err) {
                    console.error("Polling error:", err);
                }
            },

            /**
             * DB Seen Logic: Explicitly updates the 'is_read' status via AJAX.
             */
            async dynamicallyMarkRead() {
                // DB synchronization is strictly for 1-on-1 direct messages to keep seen status accurate.
                if (!this.selectedUserId || this.isGroupChat) return;
                
                try {
                    // Call new markAsRead endpoint
                    await fetch(`/messages/${this.selectedUserId}/mark-as-read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    // NOTE: There's no need to update dynamic seen text here. whispers handle dynamic UI seen text dynamicly.
                    // This method strictly synchronizes the backend database so 'seen' is correct on page reloads.
                    
                } catch (err) {
                    console.error("Failed to dynamically mark DB seen:", err);
                }
            },

            // ==========================================
            // 4. MESSAGE SENDING (Optimistic & Dynamic)
            // ==========================================

            async sendMessage(receiverId) {
                if (!this.messageInput.trim()) return;
                
                const text = this.messageInput;
                this.messageInput = ''; 
                this.thisUserIsTyping = true; // Sender-side 'Sending...' lock

                const chatMessages = document.getElementById('chat-messages');
                
                // Optimistically insert your new bubble to the DOM instantly
                // augmented optimism bubble HTML to support perfect 'Seen' status dynamic tracing
                chatMessages.insertAdjacentHTML('beforeend', `
                    <div class="text-right mb-4 w-full js-optimistic-my-bubble">
                        <span class="inline-block p-3 px-4 rounded-2xl shadow-sm text-sm bg-[#6d0101] text-white rounded-br-none">
                            ${text}
                        </span>
                        <div class="text-[10px] text-gray-400 mt-1 js-sent-msg-seen-container">
                            Just now <span class="js-realtime-seen-text"></span>
                        </div>
                    </div>
                `);

                const sidebarText = document.getElementById(`sidebar-text-${this.selectedUserId}`);
                if (sidebarText) {
                    sidebarText.innerText = "You: " + text;
                    sidebarText.classList.remove('font-semibold', 'text-gray-900');
                    sidebarText.classList.add('text-gray-500');
                }

                // Immediately scroll down
                this.$nextTick(() => { 
                    const container = document.getElementById('message-container');
                    if(container) container.scrollTop = container.scrollHeight; 
                });

                // --- SMART Front-end AI Dots ---
                const lowercaseText = text.toLowerCase();
                const aiKeywords = ['tuition', 'fee', 'password', 'schedule', 'term', 'when', 'how much', 'date', 'event', 'start', 'end'];
                const complexKeywords = ['concern', 'grade', 'bully', 'problem', 'help', 'anak', 'absent', 'sick'];
                
                const triggersAI = aiKeywords.some(keyword => lowercaseText.includes(keyword));
                const isComplex = complexKeywords.some(keyword => lowercaseText.includes(keyword));
                
                const senderRole = '{{ strtolower(auth()->user()->role) }}';
                const receiverRole = '{{ strtolower($selectedUser->role ?? '') }}';
                const teacherRoles = ['teacher', 'adviser', 'faculty', 'instructor'];
                const aiShouldRespond = (receiverRole === 'admin') || (senderRole === 'parent' && teacherRoles.includes(receiverRole));
                
                // Only show dots if it's the right role, NOT a group chat, HAS keywords, and is NOT complex.
                if (aiShouldRespond && !this.isGroupChat && triggersAI && !isComplex) {
                    this.otherUserIsTyping = true;  
                    this.isSending = true; // Lock the send button
                    
                    // CRITICAL: Force browser to draw dots before freezing for the API call
                    await this.$nextTick(); 
                } else {
                    this.otherUserIsTyping = false;
                    this.isSending = true; // Lock the send button
                }

                try {
                    const response = await fetch('{{ route('messages.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json' // Crucial: Expect JSON back
                        },
                        body: JSON.stringify({
                            receiver_id: receiverId,
                            message: text
                        })
                    });

                    if (response.ok) {
                        const jsonRes = await response.json();
                        
                        // RESTORE NORMAL CONVERSATION FLOW - NO RELOAD!
                        
                        // 👇 FIXED: GUARANTEED RESET (Lines 460-466) 👇
                        // We successfully sent. Unlock everything immediately.
                        this.isSending = false;
                        this.thisUserIsTyping = false; 
                        
                        // We set this to false every time the fetch call completes, 
                        // whether the AI answered or not. This forces the fake AI 
                        // dots to disappear. The polling loop HEARTBEAT (every 4s) 
                        // will pick up the real AI bubble naturellement.
                        this.otherUserIsTyping = false; 

                        // The code block `if (jsonRes.ai_responded) { ... } else { this.otherUserIsTyping = false; }` 
                        // was removed. This single line replacement guarantees the fix.

                    } else {
                        console.error("Message failed to send", response);
                        this.isSending = false;
                        this.thisUserIsTyping = false;
                        this.otherUserIsTyping = false;
                    }
                } catch (err) {
                    console.error("Message failed to send", err);
                    this.isSending = false;
                    this.thisUserIsTyping = false;
                    this.otherUserIsTyping = false;
                }
            },

            sendTypingWhisper() {
                if (window.Echo && this.selectedUserId) {
                    window.Echo.private(`chat.${this.selectedUserId}`)
                        .whisper('typing', {
                            senderId: this.myId
                        });
                }
            },
        }));
    });
</script>
@endsection