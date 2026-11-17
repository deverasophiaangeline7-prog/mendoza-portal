@extends('layouts.navigation')

@section('title', 'Messages - Mendoza Academy')

@section('content')
@if(request()->query('deleted') == 1 || session('success'))
    <div id="brutal-toast" style="position: fixed; bottom: 40px; right: 40px; z-index: 99999; background-color: #43e276; color: black; border: 4px solid black; border-radius: 12px; box-shadow: 8px 8px 0px 0px black; padding: 16px 28px; font-family: system-ui, sans-serif; font-weight: 900; font-size: 16px; text-transform: uppercase; display: flex; align-items: center; gap: 12px; letter-spacing: 0.5px; transition: opacity 0.4s ease;">
        <i class="fa-solid fa-circle-check" style="font-size: 22px;"></i>
        <span>
            {{ session('success') ?? 'Group Deleted!' }}
        </span>
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
            const url = new URL(window.location);
            if (url.searchParams.has('deleted')) {
                url.searchParams.delete('deleted');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }
        }
    </script>
@endif

<!-- ERROR TOAST -->
@if($errors->any())
    <div id="brutal-toast-error" style="position: fixed; bottom: 40px; right: 40px; z-index: 99999; background-color: #ff4747; color: white; border: 4px solid black; border-radius: 12px; box-shadow: 8px 8px 0px 0px black; padding: 16px 28px; font-family: system-ui, sans-serif; font-weight: 900; font-size: 16px; display: flex; align-items: center; gap: 12px; letter-spacing: 0.5px; transition: opacity 0.4s ease;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 22px;"></i>
        <span>
            {{ $errors->first() }}
        </span>
    </div>
    
    <script>
        setTimeout(() => {
            let errorToast = document.getElementById('brutal-toast-error');
            if (errorToast) {
                errorToast.style.opacity = '0';
                setTimeout(() => { errorToast.style.display = 'none'; }, 400); 
            }
        }, 4000);
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

    [x-cloak] { display: none !important; }
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
                        <button @click="dropdownOpen = false; createGroupModal = true" class="w-full text-left block px-4 py-3 text-sm text-gray-800 font-bold hover:bg-gray-100 transition-colors">
                            <i class="fa-solid fa-users mr-2 text-[#6d0101]"></i> Create a group chat
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="overflow-y-auto flex-1">
            @forelse($users as $user)
                @php
                    $hasUnread = $user->unreadMessagesCount() > 0;
                    $latestMsg = $user->latestMessageWithAuthUser();
                    $isGroupChat = !is_null($user->custom_name);
                    $isOfficialGC = str_contains(strtoupper($user->custom_name ?? ''), '(OFFICIAL)');
                    
                    $isActive = (isset($selectedUser) && $selectedUser->user_id == $user->user_id);
                    
                    if ($hasUnread) {
                        $baseBg = 'bg-blue-50/70 hover:bg-blue-100/70';
                    } else {
                        
                        $baseBg = $isActive ? 'bg-gray-50' : 'bg-white hover:bg-gray-50';
                    }
                    
                    $activeBorder = $isActive ? 'border-l-4 border-[#6d0101]' : 'border-l-4 border-transparent';
                @endphp
                <a href="{{ route('messages.show', ['id' => $user->user_id]) }}" 
                   id="sidebar-item-{{ $user->user_id }}"
                   data-is-group="{{ $isGroupChat ? 'true' : 'false' }}"
                   data-is-official="{{ $isOfficialGC ? 'true' : 'false' }}"
                   x-show="searchQuery === '' || '{{ strtolower(addslashes($user->custom_name ?? $user->name)) }}'.includes(searchQuery.toLowerCase())"
                   class="block p-4 border-b border-gray-200 transition {{ $baseBg }} {{ $activeBorder }}">
                    <div class="flex items-center">
                        @php
                            $photoName = $user->custom_name ?? $user->name;
                            $photoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($photoName) . '&background=f3f4f6&color=374151';
                            if ($user->profile_photo_path) {
                                $photoUrl = str_starts_with($user->profile_photo_path, 'profile_photos') 
                                    ? asset('storage/' . $user->profile_photo_path) 
                                    : asset($user->profile_photo_path);
                            }
                        @endphp
                        <div class="relative flex-shrink-0 mr-3">
                            <img src="{{ $photoUrl }}" class="w-12 h-12 rounded-full border object-cover" alt="User">
                            @if($isGroupChat)
                                <div class="absolute -bottom-1 -right-1 bg-gray-700 text-white rounded-full w-5 h-5 flex items-center justify-center border-2 border-white shadow-sm"><i class="fa-solid fa-users text-[9px]"></i></div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline">
                                <span class="truncate flex items-center gap-1 min-w-0 {{ $hasUnread ? 'font-bold text-black' : 'font-bold text-gray-900' }}">
                                    @if(isset($user->type) && $user->type === 'announcement') 📌 
                                    @elseif(isset($user->type) && $user->type === 'advisory') 🎓 
                                    @endif   
                                    <span class="truncate">{{ $user->custom_name ?? $user->name }}</span>
                                    
                                    @if($isOfficialGC) <i class="fa-solid fa-thumbtack text-[11px] text-[#6d0101] ml-1" title="Official Group"></i> @endif
                                    
                                    <span id="sidebar-badge-{{ $user->user_id }}" 
                                          class="bg-red-600 text-white rounded-full px-2 py-0.5 text-[10px] font-bold ml-1 flex-shrink-0 {{ $hasUnread ? '' : 'hidden' }}">
                                        {{ $user->unreadMessagesCount() }}
                                    </span>
                                </span>
                            </div>
                            <p id="sidebar-text-{{ $user->user_id }}" class="text-xs block mt-0.5 {{ $hasUnread ? 'text-gray-900 font-semibold' : 'text-gray-500' }}" style="display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; max-height: 1.15em; line-height: 1.15em;">
                            @if($latestMsg)
                                @if($latestMsg->sender_id == auth()->id())
                                    You: {{ $latestMsg->content }}
                                @else
                                    @php 
                                        $msgSender = \App\Models\User::find($latestMsg->sender_id);
                                        $prefixName = '';
                                        if ($msgSender) {
                                            // If they are an admin, say 'Admin:', otherwise use their first name
                                            $prefixName = in_array(strtolower($msgSender->role), ['admin', 'superadmin']) ? 'Admin' : strtok($msgSender->name, " ");
                                        }
                                    @endphp
                                    {{ $prefixName ? $prefixName . ': ' : '' }}{{ $latestMsg->content }}
                                @endif
                            @else
                                No messages yet...
                            @endif
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

                    @php
                        $headerName = $selectedUser->custom_name ?? $selectedUser->name;
                        $headerPhotoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($headerName) . '&background=f3f4f6&color=374151';
                        if ($selectedUser->profile_photo_path) {
                            $headerPhotoUrl = str_starts_with($selectedUser->profile_photo_path, 'profile_photos') 
                                ? asset('storage/' . $selectedUser->profile_photo_path) 
                                : asset($selectedUser->profile_photo_path);
                        }
                    @endphp
                    <img src="{{ $headerPhotoUrl }}" class="w-10 h-10 rounded-full mr-3 border object-cover {{ !is_null($selectedUser->custom_name) ? 'js-group-header-photo' : '' }}" alt="User">
                    <div>
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            {{ $selectedUser->custom_name ?? $selectedUser->name }}
                        </h3>
                        @if(!is_null($selectedUser->custom_name))
                            @if(isset($isGroupArchived) && $isGroupArchived)
                                <span class="text-xs text-orange-500 flex items-center mt-0.5"><span class="w-2 h-2 bg-orange-500 rounded-full mr-1"></span> Archived Group</span>
                            @else
                                <span class="text-xs text-blue-500 flex items-center mt-0.5"><span class="w-2 h-2 bg-blue-500 rounded-full mr-1"></span> Group Chat</span>
                            @endif
                        @else
                            @if($selectedUser->isOnline())
                                <span class="text-xs text-green-500 flex items-center mt-0.5"><span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span> Active Now</span>
                            @else
                                <span class="text-xs text-gray-400 flex items-center mt-0.5"><span class="w-2 h-2 bg-gray-400 rounded-full mr-1"></span> Offline</span>
                            @endif
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
                        
                        <!-- See Members -->
                        <button type="button" @click="groupMenuOpen = false; openMembersModal()" class="w-full text-left px-4 py-3 text-sm text-gray-800 font-bold hover:bg-gray-50 transition-colors">
                            <i class="fa-solid fa-users mr-2 text-[#6d0101]"></i> See Members
                        </button>

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
                        @php
                            // GROUP CHAT: Find the absolute latest message ID read by EACH user
                            $latestReadPerUser = [];
                            if (isset($selectedUser) && !is_null($selectedUser->custom_name)) {
                                foreach($messages as $m) {
                                    foreach($m->readers as $r) {
                                        if (!isset($latestReadPerUser[$r->user_id]) || $m->id > $latestReadPerUser[$r->user_id]) {
                                            $latestReadPerUser[$r->user_id] = $m->id;
                                        }
                                    }
                                }
                            }
                            // 1-ON-1: Find the absolute last message in the entire conversation
                            $absoluteLastMsgId = collect($messages)->last()->id ?? null;
                        @endphp
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
                                <div id="msg-bubble-{{ $message->id }}" class="mb-6 w-full relative {{ $message->sender_id === auth()->user()->user_id ? 'text-right flex flex-col items-end' : 'text-left flex items-end gap-2' }}">
                                    
                                    @if($message->sender_id !== auth()->user()->user_id)
                                        @php
                                            $sender = \App\Models\User::find($message->sender_id);
                                            $senderPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($sender->name ?? 'User') . '&background=f3f4f6&color=374151';
                                            if ($sender && $sender->profile_photo_path) {
                                                $senderPhoto = str_starts_with($sender->profile_photo_path, 'profile_photos') ? asset('storage/' . $sender->profile_photo_path) : asset($sender->profile_photo_path);
                                            }
                                        @endphp
                                        <img src="{{ $senderPhoto }}" title="{{ $sender->name ?? 'User' }}" class="w-8 h-8 rounded-full border object-cover mb-1 shadow-sm flex-shrink-0 cursor-default">
                                    @endif

                                    <div class="flex flex-col {{ $message->sender_id === auth()->user()->user_id ? 'items-end' : 'items-start' }} max-w-[85%]">
                                        
                                        @if($message->sender_id !== auth()->user()->user_id && !is_null($selectedUser->custom_name))
                                            <div class="flex items-center gap-1.5 ml-1 mb-1">
                                                <span class="text-[11px] font-bold text-gray-600">{{ $sender->name ?? 'User' }}</span>
                                                <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-full {{ ($sender->role ?? '') === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ $sender->role ?? 'User' }}
                                                </span>
                                            </div>
                                        @endif

                                        <span title="{{ $message->created_at->format('M d, Y g:i A') }}" 
                                            class="inline-block p-3 px-4 rounded-2xl shadow-sm text-sm cursor-default {{ $message->sender_id === auth()->user()->user_id ? 'bg-[#6d0101] text-white rounded-br-none' : 'bg-gray-100 text-gray-800 rounded-bl-none' }}">
                                            {!! $message->formatted_content ?? e($message->content) !!}
                                        </span>
                                        
                                        <div class="text-[10px] text-gray-400 mt-1">
                                            <span>{{ $message->created_at->format('g:i A') }}</span>
                                        </div>
                                    </div>

                                    <!-- 👁️ MESSENGER-STYLE SEEN LOGIC (PINNED TO ABSOLUTE RIGHT) -->
                                    <div class="absolute -bottom-4 right-2 js-realtime-seen-text flex items-center gap-0.5 justify-end">
                                        @if(!is_null($selectedUser->custom_name))
                                            @foreach($message->readers as $read)
                                                @if($read->user_id !== auth()->id() && $read->user_id !== $message->sender_id && isset($latestReadPerUser[$read->user_id]) && $latestReadPerUser[$read->user_id] === $message->id)
                                                    @php
                                                        $seenPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($read->user->name) . '&background=f3f4f6&color=374151';
                                                        if ($read->user->profile_photo_path) {
                                                            $seenPhoto = str_starts_with($read->user->profile_photo_path, 'profile_photos') ? asset('storage/' . $read->user->profile_photo_path) : asset($read->user->profile_photo_path);
                                                        }
                                                    @endphp
                                                    <img src="{{ $seenPhoto }}" title="Seen by {{ $read->user->name }} at {{ \Carbon\Carbon::parse($read->created_at)->format('M d, g:i A') }}" class="w-4 h-4 rounded-full border border-gray-300 ml-0.5 object-cover cursor-default shadow-sm hover:scale-125 transition-transform z-10">
                                                @endif
                                            @endforeach
                                        @else
                                            @if($message->id === $absoluteLastMsgId && $message->sender_id === auth()->id() && $message->is_read)
                                                <span class="font-bold ml-1 text-gray-500 text-[10px]">· Seen</span>
                                            @endif
                                        @endif
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
                    <div class="relative w-full">
                        <!-- MENTION DROPDOWN -->
                        <div x-show="mentionDropdownOpen" x-cloak @click.away="mentionDropdownOpen = false" class="absolute bottom-full mb-2 left-0 w-64 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden max-h-48 overflow-y-auto">
                            <template x-for="m in filteredMentions" :key="m.user_id">
                                <button type="button" @click="selectMention(m.name)" class="w-full text-left px-4 py-2.5 hover:bg-gray-100 flex items-center justify-between transition border-b border-gray-50 last:border-0">
                                    <span class="font-bold text-sm text-[#6d0101]" x-text="'@' + m.name"></span>
                                    <span class="text-[9px] uppercase text-gray-400 font-black" x-text="m.role"></span>
                                </button>
                            </template>
                        </div>
                        
                        <form @submit.prevent="sendMessage('{{ $selectedUser->user_id }}')" class="flex gap-2">
                            <input type="text" 
                                   x-ref="chatInput"
                                   x-model="messageInput"  
                                   @input="checkMentionTrigger()"
                                   :disabled="thisUserIsTyping || isSending"
                                   autocomplete="off"
                                   class="flex-1 border border-gray-300 rounded-full px-5 py-3 focus:outline-none focus:border-[#6d0101] focus:ring-1 focus:ring-[#6d0101] transition-all disabled:opacity-50" 
                                   placeholder="Type your message here..." 
                                   required>
                            <button type="submit" :disabled="thisUserIsTyping || isSending" x-text="isSending ? 'Sending...' : 'Send'" class="bg-[#6d0101] text-white px-6 py-2 rounded-full hover:bg-red-900 transition disabled:opacity-50">
                                Send
                            </button>
                        </form>
                    </div>
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
    <div x-show="newMsgModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm overflow-y-auto" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md my-auto overflow-hidden flex flex-col" @click.away="newMsgModal = false">
            <div class="p-4 border-b bg-[#6d0101] text-white flex justify-between items-center">
                <h3 class="font-bold text-lg">New Message</h3>
                <button @click="newMsgModal = false" class="hover:text-gray-300 transition text-xl">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto" x-data="{ userSearch: '' }">
                
                <!-- Search Bar -->
                <div class="relative mb-4">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
                    <input type="text" x-model="userSearch" placeholder="Search by name or role..." class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#6d0101] transition-colors">
                </div>

                <!-- Scrollable Contact List -->
                <div class="max-h-60 overflow-y-auto border-2 border-gray-100 rounded-xl divide-y divide-gray-100 relative shadow-inner bg-gray-50">
                    @if(isset($contacts) && count($contacts) > 0)
                        @php
                            $contactCollection = collect($contacts);
                            $staff = $contactCollection->filter(fn($c) => strtolower($c->role) !== 'parent');
                            $parents = $contactCollection->filter(fn($c) => strtolower($c->role) === 'parent');

                            // Strict numerical grading weight to force correct display order
                            $nm_getGradeWeight = function($name) {
                                $lower = strtolower($name);
                                if (str_contains($lower, 'nursery')) return 1;
                                if (str_contains($lower, 'kinder')) return 2;
                                if (str_contains($lower, 'prep')) return 3;
                                
                                preg_match('/(\d+)/', $lower, $matches);
                                if (isset($matches[1])) {
                                    return 10 + (int)$matches[1];
                                }
                                return 99; // Fallback
                            };

                            // Group parents by their school section
                            $parentsBySection = $parents->groupBy(function($parent) {
                                if ($parent->student && $parent->student->section) {
                                    $grade = $parent->student->section->grade_level ?? '';
                                    $secName = $parent->student->section->section_name ?? $parent->student->section->name ?? '';
                                    return trim($grade . ' - ' . $secName, ' -');
                                }
                                if ($parent->section_id) {
                                    $section = \App\Models\Section::where('section_id', $parent->section_id)->first();
                                    if ($section) {
                                        $grade = $section->grade_level ?? '';
                                        $secName = $section->section_name ?? $section->name ?? '';
                                        return trim($grade . ' - ' . $secName, ' -');
                                    }
                                }
                                return 'Other Parents';
                            })->sortBy(function($group, $sectionName) use ($nm_getGradeWeight) {
                                return $nm_getGradeWeight($sectionName);
                            });
                        @endphp

                        <!-- Staff & Admin Section -->
                        @if($staff->count() > 0)
                            @php
                                // Sort staff: Admins first, then teachers ordered by grade level
                                $sortedStaff = $staff->sort(function($a, $b) use ($nm_getGradeWeight) {
                                    $roleA = strtolower($a->role);
                                    $roleB = strtolower($b->role);

                                    $isAdminA = in_array($roleA, ['admin', 'superadmin']);
                                    $isAdminB = in_array($roleB, ['admin', 'superadmin']);

                                    if ($isAdminA && !$isAdminB) return -1;
                                    if (!$isAdminA && $isAdminB) return 1;

                                    $getTeacherGradeWeight = function($teacher) use ($nm_getGradeWeight) {
                                        $tSections = \App\Models\Section::where('teacher_id', $teacher->user_id)->get();
                                        if ($tSections->count() > 0) {
                                            $weights = [];
                                            foreach($tSections as $tSec) {
                                                $tGradeStr = trim(($tSec->grade_level ?? '') . ' ' . ($tSec->section_name ?? $tSec->name ?? ''));
                                                $weights[] = $nm_getGradeWeight($tGradeStr);
                                            }
                                            return min($weights); 
                                        }
                                        return 999; 
                                    };

                                    return $getTeacherGradeWeight($a) <=> $getTeacherGradeWeight($b);
                                });
                            @endphp

                            <div class="bg-gray-200/90 backdrop-blur-sm px-3 py-1.5 text-[10px] font-black text-gray-500 uppercase tracking-wider sticky top-0 z-10 shadow-sm" 
                                x-show="userSearch === '' || {{ json_encode($sortedStaff->values()) }}.some(c => (c.name + ' ' + c.role).toLowerCase().includes(userSearch.toLowerCase()))">
                                Staff & Admins
                            </div>
                            
                            @foreach ($sortedStaff as $contact)
                                @php
                                    $advisoryText = '';
                                    if (in_array(strtolower($contact->role), ['teacher', 'adviser', 'faculty', 'instructor'])) {
                                        $teacherSections = \App\Models\Section::where('teacher_id', $contact->user_id)->get();
                                        if ($teacherSections->count() > 0) {
                                            $hasNKP = false;
                                            $sectionStrings = [];
                                            
                                            foreach($teacherSections as $tSec) {
                                                $tGradeStr = strtolower($tSec->grade_level ?? '');
                                                if (str_contains($tGradeStr, 'nursery') || str_contains($tGradeStr, 'kinder') || str_contains($tGradeStr, 'prep')) {
                                                    $hasNKP = true;
                                                } else {
                                                    $tGrade = $tSec->grade_level ?? '';
                                                    $tSecName = $tSec->section_name ?? $tSec->name ?? '';
                                                    $sectionStrings[] = trim($tGrade . ' - ' . $tSecName, ' -');
                                                }
                                            }
                                            
                                            $finalSections = [];
                                            if ($hasNKP) $finalSections[] = 'NKP';
                                            $finalSections = array_merge($finalSections, $sectionStrings);
                                            
                                            $advisoryText = 'Adviser: ' . implode(', ', $finalSections);
                                        }
                                    }
                                @endphp
                                <a href="{{ route('messages.show', $contact->user_id) }}" 
                                   class="flex items-center p-3 hover:bg-red-50 transition-colors bg-white"
                                   x-show="userSearch === '' || '{{ strtolower(addslashes($contact->name . ' ' . $contact->role . ' ' . $advisoryText)) }}'.includes(userSearch.toLowerCase())">
                                    
                                    @php
                                        $photoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($contact->name) . '&background=f3f4f6&color=374151';
                                        if ($contact->profile_photo_path) {
                                            $photoUrl = str_starts_with($contact->profile_photo_path, 'profile_photos') 
                                                ? asset('storage/' . $contact->profile_photo_path) 
                                                : asset($contact->profile_photo_path);
                                        }
                                    @endphp
                                    <img src="{{ $photoUrl }}" class="w-10 h-10 rounded-full mr-3 border object-cover flex-shrink-0" alt="User">                                
                                    
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-gray-800 text-sm leading-tight truncate">{{ $contact->name }}</h4>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-black uppercase tracking-wider {{ in_array(strtolower($contact->role), ['admin', 'superadmin']) ? 'text-blue-600' : 'text-amber-600' }}">
                                                {{ $contact->role }}
                                            </span>
                                            @if($advisoryText)
                                                <span class="text-[10px] font-semibold text-gray-500 truncate" title="{{ $advisoryText }}">· {{ $advisoryText }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        @endif

                        <!-- Parents Grouped by School Section -->
                        @foreach($parentsBySection as $sectionName => $sectionParents)
                            <div class="bg-gray-200/90 backdrop-blur-sm px-3 py-1.5 text-[10px] font-black text-gray-500 uppercase tracking-wider sticky top-0 z-10 shadow-sm" 
                                x-show="userSearch === '' || {{ json_encode($sectionParents->values()) }}.some(c => (c.name + ' ' + c.role).toLowerCase().includes(userSearch.toLowerCase()))">
                                {{ $sectionName }}
                            </div>
                            
                            @foreach ($sectionParents as $contact)
                                <a href="{{ route('messages.show', $contact->user_id) }}" 
                                   class="flex items-center p-3 hover:bg-red-50 transition-colors bg-white"
                                   x-show="userSearch === '' || '{{ strtolower(addslashes($contact->name . ' ' .$contact->role)) }}'.includes(userSearch.toLowerCase())">
                                    
                                    @php
                                        $photoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($contact->name) . '&background=f3f4f6&color=374151';
                                        if ($contact->profile_photo_path) {
                                            $photoUrl = str_starts_with($contact->profile_photo_path, 'profile_photos') 
                                                ? asset('storage/' . $contact->profile_photo_path) 
                                                : asset($contact->profile_photo_path);
                                        }
                                    @endphp
                                    <img src="{{ $photoUrl }}" class="w-10 h-10 rounded-full mr-3 border object-cover flex-shrink-0" alt="User">
                                    
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-gray-800 text-sm leading-tight truncate">{{ $contact->name }}</h4>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-green-600">
                                                {{ $contact->role }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
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
    <div x-show="createGroupModal" x-cloak class="fixed inset-0 z-[100] flex justify-center pt-16 pb-8 px-4 bg-black/50 backdrop-blur-sm overflow-y-auto" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[85vh] overflow-hidden flex flex-col my-auto" @click.away="createGroupModal = false">            
            <div class="p-4 border-b bg-[#6d0101] text-white flex justify-between items-center flex-shrink-0">
                <h3 class="font-bold text-lg">Create Group Chat</h3>
                <button @click="createGroupModal = false" class="hover:text-gray-300 transition text-xl">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1" x-data="{ groupName: '', groupSearch: '', selectedMembers: [], photoPreview: null }">
                
                <!-- 🚨 CRITICAL: Added enctype to allow file uploads -->
                <form action="{{ route('messages.group.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Group Photo Upload (Interactive UI) -->
                    <div class="mb-5 flex flex-col items-center">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Group Photo (Optional)</label>
                        <div class="relative w-24 h-24 group cursor-pointer" @click="$refs.groupPhotoInput.click()">
                            <!-- Image Preview -->
                            <img :src="photoPreview || 'https://ui-avatars.com/api/?name=' + (groupName ? encodeURIComponent(groupName) : 'Group') + '&background=f3f4f6&color=374151'" 
                                 class="w-24 h-24 rounded-full border-[3px] border-gray-200 object-cover shadow-sm transition group-hover:border-[#6d0101]">
                            
                            <!-- Little Camera Badge -->
                            <div class="absolute bottom-0 right-0 bg-[#6d0101] text-white rounded-full w-8 h-8 flex items-center justify-center border-[3px] border-white shadow-sm">
                                <i class="fa-solid fa-camera text-xs"></i>
                            </div>
                        </div>
                        
                        <!-- Hidden File Input handled by Alpine -->
                        <input type="file" name="group_photo" x-ref="groupPhotoInput" accept="image/*" class="hidden" 
                            @change="
                                const file = $event.target.files[0];
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = (e) => photoPreview = e.target.result;
                                    reader.readAsDataURL(file);
                                }
                            ">
                    </div>

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
                    <div class="max-h-64 overflow-y-auto border-2 border-gray-100 rounded-xl divide-y divide-gray-100 mb-6 shadow-inner bg-gray-50 relative">
                        @if(isset($contacts) && count($contacts) > 0)
                            @php
                                $contactCollection = collect($contacts);
                                $staff = $contactCollection->filter(fn($c) => strtolower($c->role) !== 'parent');
                                $parents = $contactCollection->filter(fn($c) => strtolower($c->role) === 'parent');

                                // Strict numerical grading weight to force correct display order
                                $getGradeWeight = function($name) {
                                    $lower = strtolower($name);
                                    if (str_contains($lower, 'nursery')) return 1;
                                    if (str_contains($lower, 'kinder')) return 2;
                                    if (str_contains($lower, 'prep')) return 3;
                                    
                                    // Extract number for grades 1 through 12
                                    preg_match('/(\d+)/', $lower, $matches);
                                    if (isset($matches[1])) {
                                        return 10 + (int)$matches[1];
                                    }
                                    
                                    return 99; // Fallback for unmatched names
                                };

                                // Group parents by their school section
                                $parentsBySection = $parents->groupBy(function($parent) {
                                    if ($parent->student && $parent->student->section) {
                                        $grade = $parent->student->section->grade_level ?? '';
                                        $secName = $parent->student->section->section_name ?? $parent->student->section->name ?? '';
                                        return trim($grade . ' - ' . $secName, ' -');
                                    }
                                    if ($parent->section_id) {
                                        $section = \App\Models\Section::where('section_id', $parent->section_id)->first();
                                        if ($section) {
                                            $grade = $section->grade_level ?? '';
                                            $secName = $section->section_name ?? $section->name ?? '';
                                            return trim($grade . ' - ' . $secName, ' -');
                                        }
                                    }
                                    return 'Other Parents';
                                })->sortBy(function($group, $sectionName) use ($getGradeWeight) {
                                    return $getGradeWeight($sectionName);
                                });
                            @endphp

                            <!-- Staff & Admin Section -->
                            @if($staff->count() > 0)
                                @php
                                    // Sort staff: Admins first, then teachers ordered by grade level
                                    $sortedStaff = $staff->sort(function($a, $b) use ($getGradeWeight) {
                                        $roleA = strtolower($a->role);
                                        $roleB = strtolower($b->role);

                                        // Admins always come before teachers/staff
                                        $isAdminA = in_array($roleA, ['admin', 'superadmin']);
                                        $isAdminB = in_array($roleB, ['admin', 'superadmin']);

                                        if ($isAdminA && !$isAdminB) return -1;
                                        if (!$isAdminA && $isAdminB) return 1;

                                        // If both are teachers, sort by their lowest advisory section grade level
                                        $getTeacherGradeWeight = function($teacher) use ($getGradeWeight) {
                                            $tSections = \App\Models\Section::where('teacher_id', $teacher->user_id)->get();
                                            if ($tSections->count() > 0) {
                                                $weights = [];
                                                foreach($tSections as $tSec) {
                                                    $tGradeStr = trim(($tSec->grade_level ?? '') . ' ' . ($tSec->section_name ?? $tSec->name ?? ''));
                                                    $weights[] = $getGradeWeight($tGradeStr);
                                                }
                                                return min($weights); // Sorts them based on their lowest assigned grade
                                            }
                                            return 999; // Teachers without advisory go to the bottom
                                        };

                                        return $getTeacherGradeWeight($a) <=> $getTeacherGradeWeight($b);
                                    });
                                @endphp

                                <div class="bg-gray-200/90 backdrop-blur-sm px-3 py-1.5 text-[10px] font-black text-gray-500 uppercase tracking-wider sticky top-0 z-10 shadow-sm" 
                                    x-show="groupSearch === '' || {{ json_encode($sortedStaff->values()) }}.some(c => (c.name + ' ' + c.role).toLowerCase().includes(groupSearch.toLowerCase()))">
                                    Staff & Admins
                                </div>
                                @foreach ($sortedStaff as $contact)
                                    @php
                                        $advisoryText = '';
                                        if (in_array(strtolower($contact->role), ['teacher', 'adviser', 'faculty', 'instructor'])) {
                                            $teacherSections = \App\Models\Section::where('teacher_id', $contact->user_id)->get();
                                            if ($teacherSections->count() > 0) {
                                                $hasNKP = false;
                                                $sectionStrings = [];
                                                
                                                foreach($teacherSections as $tSec) {
                                                    $tGradeStr = strtolower($tSec->grade_level ?? '');
                                                    // Detect if any of the assigned sections belong to the NKP cluster
                                                    if (str_contains($tGradeStr, 'nursery') || str_contains($tGradeStr, 'kinder') || str_contains($tGradeStr, 'prep')) {
                                                        $hasNKP = true;
                                                    } else {
                                                        $tGrade = $tSec->grade_level ?? '';
                                                        $tSecName = $tSec->section_name ?? $tSec->name ?? '';
                                                        $sectionStrings[] = trim($tGrade . ' - ' . $tSecName, ' -');
                                                    }
                                                }
                                                
                                                $finalSections = [];
                                                // Group all early childhood into a single NKP tag
                                                if ($hasNKP) {
                                                    $finalSections[] = 'NKP';
                                                }
                                                // Append any older grades they might also advise (e.g., Grade 1)
                                                $finalSections = array_merge($finalSections, $sectionStrings);
                                                
                                                $advisoryText = 'Adviser: ' . implode(', ', $finalSections);
                                            }
                                        }
                                    @endphp
                                    <label class="flex items-center p-3 hover:bg-red-50 cursor-pointer transition-colors bg-white"
                                        x-show="groupSearch === '' || '{{ strtolower(addslashes($contact->name . ' ' . $contact->role . ' ' . $advisoryText)) }}'.includes(groupSearch.toLowerCase())">
                                        
                                        <input type="checkbox" name="members[]" value="{{ $contact->user_id }}" x-model="selectedMembers" class="rounded text-[#6d0101] border-gray-300 focus:ring-[#6d0101] w-4 h-4 mr-3">
                                        
                                        @php
                                            $groupPhotoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($contact->name) . '&background=f3f4f6&color=374151';
                                            if ($contact->profile_photo_path) {
                                                $groupPhotoUrl = str_starts_with($contact->profile_photo_path, 'profile_photos') 
                                                    ? asset('storage/' . $contact->profile_photo_path) 
                                                    : asset($contact->profile_photo_path);
                                            }
                                        @endphp
                                        <img src="{{ $groupPhotoUrl }}" class="w-9 h-9 rounded-full mr-3 border object-cover" alt="User">
                                        
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-gray-800 text-sm leading-tight truncate">{{ $contact->name }}</h4>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-black uppercase tracking-wider {{ in_array(strtolower($contact->role), ['admin', 'superadmin']) ? 'text-blue-600' : 'text-amber-600' }}">
                                                    {{ $contact->role }}
                                                </span>
                                                @if($advisoryText)
                                                    <span class="text-[10px] font-semibold text-gray-500 truncate" title="{{ $advisoryText }}">· {{ $advisoryText }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            @endif

                            <!-- Parents Grouped by School Section -->
                            @foreach($parentsBySection as $sectionName => $sectionParents)
                                <div class="bg-gray-200/90 backdrop-blur-sm px-3 py-1.5 text-[10px] font-black text-gray-500 uppercase tracking-wider sticky top-0 z-10 shadow-sm" 
                                    x-show="groupSearch === '' || {{ json_encode($sectionParents->values()) }}.some(c => (c.name + ' ' + c.role).toLowerCase().includes(groupSearch.toLowerCase()))">
                                    {{ $sectionName }}
                                </div>
                                
                                @foreach ($sectionParents as $contact)
                                    <label class="flex items-center p-3 hover:bg-red-50 cursor-pointer transition-colors bg-white"
                                        x-show="groupSearch === '' || '{{ strtolower(addslashes($contact->name . ' ' .$contact->role)) }}'.includes(groupSearch.toLowerCase())">
                                        
                                        <input type="checkbox" name="members[]" value="{{ $contact->user_id }}" x-model="selectedMembers" class="rounded text-[#6d0101] border-gray-300 focus:ring-[#6d0101] w-4 h-4 mr-3">
                                        
                                        @php
                                            $groupPhotoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($contact->name) . '&background=f3f4f6&color=374151';
                                            if ($contact->profile_photo_path) {
                                                $groupPhotoUrl = str_starts_with($contact->profile_photo_path, 'profile_photos') 
                                                    ? asset('storage/' . $contact->profile_photo_path) 
                                                    : asset($contact->profile_photo_path);
                                            }
                                        @endphp
                                        <img src="{{ $groupPhotoUrl }}" class="w-9 h-9 rounded-full mr-3 border object-cover" alt="User">
                                        
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-sm leading-tight">{{ $contact->name }}</h4>
                                            <span class="text-[10px] font-black uppercase tracking-wider text-green-600">
                                                {{ $contact->role }}
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
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

    <!-- SEE MEMBERS MODAL -->
    <div x-show="membersModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="p-4 border-b bg-[#6d0101] text-white flex justify-between items-center">
                <h3 class="font-bold text-lg" x-text="membersData.group_name || 'Group Members'"></h3>
                <button @click="membersModal = false" class="hover:text-gray-300 text-xl"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="p-4 max-h-80 overflow-y-auto divide-y divide-gray-100">
                <template x-if="membersLoading">
                    <p class="text-center text-sm text-gray-400 py-6">Loading members...</p>
                </template>
                <template x-if="!membersLoading && membersData.members.length === 0">
                    <p class="text-center text-sm text-gray-400 py-6">Walang miyembro.</p>
                </template>
                <template x-for="m in membersData.members" :key="m.user_id">
                    <div class="flex items-center py-3">
                        <img :src="m.photo" class="w-10 h-10 rounded-full border object-cover mr-3 flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-gray-800 text-sm truncate" x-text="m.name"></h4>
                            <span class="text-[10px] font-black uppercase tracking-wider"
                                  :class="m.role === 'admin' ? 'text-blue-600' : (m.role === 'teacher' ? 'text-amber-600' : 'text-green-600')"
                                  x-text="m.role"></span>
                        </div>
                        <span x-show="m.is_creator" class="text-[10px] font-bold text-gray-400 ml-2">Creator</span>
                    </div>
                </template>
            </div>

            <div class="p-4 border-t flex justify-between items-center gap-3">
                <button x-show="membersData.can_edit_photo" @click="openPhotoModal()"
                        class="bg-[#6d0101] text-white px-4 py-2 rounded-xl font-bold hover:bg-red-900 transition text-sm">
                    <i class="fa-solid fa-camera mr-1"></i> Change Photo
                </button>
                <button @click="membersModal = false" class="ml-auto bg-gray-200 text-gray-800 px-4 py-2 rounded-xl font-bold hover:bg-gray-300 transition text-sm">Close</button>
            </div>
        </div>
    </div>

    <!-- UPDATE GROUP PHOTO MODAL -->
    <div x-show="photoModal" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden flex flex-col p-6 text-center" @click.away="photoModal = false">
            <h3 class="font-bold text-lg text-gray-800 mb-4">Update Group Photo</h3>

            <div class="relative w-28 h-28 mx-auto mb-4 cursor-pointer" @click="$refs.newGroupPhoto.click()">
                <img :src="newPhotoPreview || membersData.group_photo" class="w-28 h-28 rounded-full border-[3px] border-gray-200 object-cover">
                <div class="absolute bottom-0 right-0 bg-[#6d0101] text-white rounded-full w-9 h-9 flex items-center justify-center border-[3px] border-white">
                    <i class="fa-solid fa-camera text-sm"></i>
                </div>
            </div>

            <input type="file" accept="image/*" class="hidden" x-ref="newGroupPhoto"
                   @change="
                       const f = $event.target.files[0];
                       if (f) {
                           selectedPhotoFile = f;
                           const r = new FileReader();
                           r.onload = e => newPhotoPreview = e.target.result;
                           r.readAsDataURL(f);
                       }
                   ">

            <div class="flex justify-center gap-3 mt-2">
                <button @click="photoModal = false" type="button" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2.5 rounded-xl font-bold hover:bg-gray-200 transition">Cancel</button>
                <button @click="uploadGroupPhoto()" :disabled="!selectedPhotoFile || photoUploading"
                        x-text="photoUploading ? 'Uploading...' : 'Save'"
                        class="flex-1 bg-[#6d0101] text-white px-4 py-2.5 rounded-xl font-bold hover:bg-red-900 transition disabled:opacity-50"></button>
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
            membersModal: false,
            membersLoading: false,
            membersData: { group_name: '', members: [], can_edit_photo: false, group_photo: '' },
            photoModal: false,
            photoUploading: false,
            newPhotoPreview: null,
            selectedPhotoFile: null,
            messageInput: '',
            mentionDropdownOpen: false,
            mentionSearch: '',
            mentionMembers: [],
            filteredMentions: [],
            thisUserIsTyping: false, 
            otherUserIsTyping: false, 
            isSending: false,
            myId: '{{ auth()->user()->user_id }}',
            selectedUserId: '{{ isset($selectedUser) ? $selectedUser->user_id : "" }}',
            selectedUserName: '{{ isset($selectedUser) ? (isset($selectedUser->custom_name) ? $selectedUser->custom_name : $selectedUser->name) : "" }}',
            isGroupChat: '{{ is_null($selectedUser->custom_name ?? null) ? "false" : "true" }}' === 'true',
            
            otherUserTypingTimer: null,
            pollingInterval: null, 
            sidebarPollingInterval: null,

            init() {
                const container = document.getElementById('message-container');
                if (container) container.scrollTop = container.scrollHeight;

                if (this.selectedUserId && this.myId) {
                    this.dynamicallyMarkRead();
                    this.fetchMentionMembers();

                    this.pollingInterval = setInterval(() => {
                        this.pollNewMessages();
                    }, 4000); 
                }

                this.sidebarPollingInterval = setInterval(() => {
                    this.pollSidebarUpdates();
                }, 4000);
            },

            moveToTop(sidebarItem) {
                if (!sidebarItem) return;
                const isOfficial = sidebarItem.getAttribute('data-is-official') === 'true';
                const parent = sidebarItem.parentNode;
                
                if (isOfficial) {
                    parent.prepend(sidebarItem); 
                } else {
                    const firstNonOfficial = Array.from(parent.children).find(el => el.tagName === 'A' && el.getAttribute('data-is-official') === 'false' && el !== sidebarItem);
                    if (firstNonOfficial) parent.insertBefore(sidebarItem, firstNonOfficial); 
                    else parent.appendChild(sidebarItem);
                }
            },

            async fetchMentionMembers() {
                if (!this.isGroupChat || !this.selectedUserId) return;
                try {
                    const res = await fetch(`/messages/group/${this.selectedUserId}/members`);
                    const data = await res.json();
                    if (data.success) {
                        this.mentionMembers = data.members;
                        // Isama si @everybody sa listahan
                        this.mentionMembers.unshift({ user_id: 'all', name: 'everybody', role: 'all' });
                    }
                } catch (e) {}
            },

            checkMentionTrigger() {
                if (!this.isGroupChat) return;
                // I-check kung may tinatype na @ sa huli
                const match = this.messageInput.match(/@([a-zA-Z0-9 ]*)$/);
                if (match) {
                    this.mentionSearch = match[1].toLowerCase();
                    this.filteredMentions = this.mentionMembers.filter(m => m.name.toLowerCase().includes(this.mentionSearch));
                    this.mentionDropdownOpen = this.filteredMentions.length > 0;
                } else {
                    this.mentionDropdownOpen = false;
                }
            },

            selectMention(name) {
                // Palitan yung tinaype ng user ng kumpletong pangalan
                this.messageInput = this.messageInput.replace(/@([a-zA-Z0-9 ]*)$/, `@${name} `);
                this.mentionDropdownOpen = false;
                this.$refs.chatInput.focus(); // I-balik ang cursor sa chat input
            },

            async openMembersModal() {
                if (!this.selectedUserId) return;
                this.membersModal = true;
                this.membersLoading = true;
                this.membersData = { group_name: '', members: [], can_edit_photo: false, group_photo: '' };
                try {
                    const res = await fetch(`/messages/group/${this.selectedUserId}/members`);
                    const data = await res.json();
                    if (data.success) {
                        const headerImg = document.querySelector('.js-group-header-photo');
                        this.membersData = {
                            group_name: data.group_name,
                            members: data.members,
                            can_edit_photo: data.can_edit_photo,
                            group_photo: headerImg ? headerImg.src : '',
                        };
                    }
                } catch (e) {}
                this.membersLoading = false;
            },

            openPhotoModal() {
                this.photoModal = true;
                this.newPhotoPreview = null;
                this.selectedPhotoFile = null;
            },

            async uploadGroupPhoto() {
                if (!this.selectedPhotoFile) return;
                this.photoUploading = true;

                const form = new FormData();
                form.append('group_photo', this.selectedPhotoFile);
                form.append('_token', '{{ csrf_token() }}');

                try {
                    const res = await fetch(`/messages/group/${this.selectedUserId}/photo`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: form,
                    });
                    const data = await res.json();

                    if (data.success) {
                        const newUrl = data.new_path + '?t=' + Date.now();

                        // Update header photo
                        const headerImg = document.querySelector('.js-group-header-photo');
                        if (headerImg) headerImg.src = newUrl;

                        // Update sidebar photo
                        const sidebarImg = document.querySelector(`#sidebar-item-${this.selectedUserId} img`);
                        if (sidebarImg) sidebarImg.src = newUrl;

                        this.membersData.group_photo = newUrl;
                        this.photoModal = false;
                    } else {
                        alert(data.message || 'Upload failed.');
                    }
                } catch (e) {
                    alert('Upload failed.');
                }

                this.photoUploading = false;
            },

            async pollNewMessages() {
                if (!this.selectedUserId) return;
                
                try {
                    let lastId = 0;
                    const bubbles = document.querySelectorAll('[id^="msg-bubble-"]');
                    bubbles.forEach(bubble => {
                        const idNum = parseInt(bubble.id.replace('msg-bubble-', ''));
                        if (!isNaN(idNum) && idNum > lastId) {
                            lastId = idNum;
                        }
                    });

                    // 👈 BAGO: May &t=Date.now() at cache: no-store para iwas browser cache lag!
                    const response = await fetch(`/messages/${this.selectedUserId}/poll?last_id=${lastId}&t=${Date.now()}`, { cache: 'no-store' });
                    
                    // 👈 BAGO: Kung pinatay ng Single Session ang account mo, i-a-auto kick ka pabalik sa login!
                    if (response.status === 401 || response.status === 419) {
                        window.location.reload();
                        return;
                    }
                    if (!response.ok) return;
                    
                    const data = await response.json();
                    const newMessagesData = data.messages || [];
                    
                    if (this.isGroupChat && data.gc_latest_readers && data.gc_latest_readers.length > 0) {
                        const unreadStatusSpans = document.querySelectorAll('.js-realtime-seen-text');
                        if (unreadStatusSpans.length > 0) {
                            unreadStatusSpans.forEach(span => span.innerHTML = '');
                            const lastSpan = unreadStatusSpans[unreadStatusSpans.length - 1]; 
                            let avatarsHtml = '';
                            data.gc_latest_readers.forEach(reader => {
                                avatarsHtml += `<img src="${reader.photo}" title="Seen by ${reader.name}" class="w-4 h-4 rounded-full border border-gray-300 ml-0.5 object-cover cursor-default shadow-sm hover:scale-125 transition-transform z-10">`;
                            });
                            lastSpan.innerHTML = avatarsHtml;
                        }
                    } else if (data.last_sent_read && !this.isGroupChat) {
                        const unreadStatusSpans = document.querySelectorAll('.js-realtime-seen-text');
                        if (unreadStatusSpans.length > 0) {
                            unreadStatusSpans.forEach(span => span.innerHTML = '');
                            const lastSpan = unreadStatusSpans[unreadStatusSpans.length - 1];
                            lastSpan.innerHTML = '<span class="font-bold text-gray-500 text-[10px]">· Seen</span>';
                        }
                    }

                    if (newMessagesData.length > 0) {
                        const chatMessagesWrapper = document.getElementById('chat-messages');
                        const emptyState = document.getElementById('empty-chat-state');
                        if (emptyState) emptyState.remove();

                        let needsMarkRead = false;

                        newMessagesData.forEach(msg => {
                            const existingMsgBubble = document.getElementById(`msg-bubble-${msg.id}`);
                            if (!existingMsgBubble) {
                                needsMarkRead = true; 
                                
                                const isMe = String(msg.sender_id) === String(this.myId);
                                if (isMe) {
                                    const tempBubbles = document.querySelectorAll('.js-optimistic-my-bubble');
                                    let handled = false;
                                    for (let ob of tempBubbles) {
                                        if (ob.id.startsWith('temp-msg-') && ob.textContent.includes(msg.content)) {
                                            ob.id = `msg-bubble-${msg.id}`; 
                                            ob.classList.remove('js-optimistic-my-bubble'); 
                                            handled = true;
                                            break;
                                        }
                                    }
                                    if (handled) return; 
                                }
                                let bubbleHtml = '';

                                if (isMe) {
                                    bubbleHtml = `
                                        <div id="msg-bubble-${msg.id}" class="mb-6 w-full relative text-right flex flex-col items-end js-dynamically-fetched-bubble">
                                            <div class="flex flex-col items-end max-w-[85%]">
                                                <span class="inline-block p-3 px-4 rounded-2xl shadow-sm text-sm bg-[#6d0101] text-white rounded-br-none">
                                                    ${msg.formatted_content || msg.content}
                                                </span>
                                                <div class="text-[10px] text-gray-400 mt-1">
                                                    <span>${msg.time_formatted}</span>
                                                </div>
                                            </div>
                                            <div class="absolute -bottom-4 right-2 js-realtime-seen-text flex items-center gap-0.5 justify-end"></div>
                                        </div>
                                    `;
                                } else {
                                    let senderInfoHtml = '';
                                    if (this.isGroupChat) {
                                        const roleBadgeClass = msg.sender_role === 'admin' ? 'bg-blue-100 text-blue-700' : (msg.sender_role === 'teacher' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500');
                                        senderInfoHtml = `
                                            <div class="flex items-center gap-1.5 ml-1 mb-1">
                                                <span class="text-[11px] font-bold text-gray-600">${msg.sender_name}</span>
                                                <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-full ${roleBadgeClass}">
                                                    ${msg.sender_role}
                                                </span>
                                            </div>
                                        `;
                                    }
                                    
                                    const avatarHtml = `<img src="${msg.sender_photo}" title="${msg.sender_name}" class="w-8 h-8 rounded-full border object-cover mb-1 shadow-sm flex-shrink-0 cursor-default">`;

                                    bubbleHtml = `
                                        <div id="msg-bubble-${msg.id}" class="mb-6 w-full relative text-left flex items-end gap-2 js-dynamically-fetched-bubble">
                                            ${avatarHtml}
                                            <div class="flex flex-col items-start max-w-[85%]">
                                                ${senderInfoHtml}
                                                <span class="inline-block p-3 px-4 rounded-2xl shadow-sm text-sm bg-gray-100 text-gray-800 rounded-bl-none">
                                                    ${msg.formatted_content || msg.content}
                                                </span>
                                                <div class="text-[10px] text-gray-400 mt-1">
                                                    <span>${msg.time_formatted}</span>
                                                </div>
                                            </div>
                                            <div class="absolute -bottom-4 right-2 js-realtime-seen-text flex items-center gap-0.5 justify-end"></div>
                                        </div>
                                    `;
                                }

                                chatMessagesWrapper.insertAdjacentHTML('beforeend', bubbleHtml);
                            }
                        });

                        const sidebarText = document.getElementById(`sidebar-text-${this.selectedUserId}`);
                        const sidebarItem = document.getElementById(`sidebar-item-${this.selectedUserId}`);

                        if (sidebarText && newMessagesData.length > 0) {
                            const latestNewMsg = newMessagesData[newMessagesData.length - 1];
                            let prefix = "";
                            
                            if (latestNewMsg.sender_id == this.myId) {
                                prefix = "You: ";
                            } else {
                                let sRole = latestNewMsg.sender_role || '{{ strtolower($selectedUser->role ?? "") }}';
                                let sName = latestNewMsg.sender_name || this.selectedUserName;
                                
                                if (sRole === 'admin' || sRole === 'superadmin') {
                                    prefix = "Admin: ";
                                } else if (sName) {
                                    prefix = sName.split(' ')[0] + ": "; 
                                }
                            }
                            
                            sidebarText.innerText = prefix + latestNewMsg.content;
                            sidebarText.classList.remove('text-gray-500');
                            sidebarText.classList.add('text-gray-900', 'font-semibold');
                        }
                        
                        if (sidebarItem && needsMarkRead && !this.isGroupChat) {
                             sidebarItem.classList.add('bg-blue-50/70');
                             sidebarItem.classList.remove('bg-white', 'hover:bg-gray-50');
                        }

                        if (sidebarItem && newMessagesData.length > 0) {
                            sidebarItem.parentNode.prepend(sidebarItem); 
                        }

                        this.$nextTick(() => { 
                            const container = document.getElementById('message-container');
                            if(container) container.scrollTop = container.scrollHeight; 
                        });

                        if (needsMarkRead) {
                            this.dynamicallyMarkRead();
                        }
                    }
                    
                } catch (err) {
                    console.error("Polling error:", err);
                }
            },

            async pollSidebarUpdates() {
                try {
                    // 👇 BAGO: Makikita mo na ito sa Console Tab mo every 4 seconds!
                    console.log("✅ Nag-poll sa sidebar...", data.sidebar);

                    const response = await fetch('/messages/sidebar/poll?t=' + Date.now(), { cache: 'no-store' });
                    
                    if (response.status === 401 || response.status === 419) {
                        window.location.reload();
                        return;
                    }
                    if (!response.ok) return;

                    const data = await response.json();
                    let totalUnread = 0; 
                    
                    // 👇 FIX: Para hindi mag-crash kahit object o array ang ibalik ng Laravel
                    const sidebarArray = Array.isArray(data.sidebar) ? data.sidebar : Object.values(data.sidebar);
                    const reversedSidebar = [...sidebarArray].reverse();

                    reversedSidebar.forEach(chat => {
                        const sidebarItem = document.getElementById(`sidebar-item-${chat.user_id}`);
                        const sidebarText = document.getElementById(`sidebar-text-${chat.user_id}`);
                        const badgeContainer = document.getElementById(`sidebar-badge-${chat.user_id}`);
                        
                        // Kung may bagong chat na wala sa sidebar, auto-refresh para lumitaw
                        if (!sidebarItem && chat.unread_count > 0) {
                            window.location.reload();
                            return;
                        }

                        if (sidebarItem && sidebarText) {
                            let displayText = chat.latest_message;
                            if (chat.is_mentioned && chat.unread_count > 0) {
                                displayText = "🔔 Mentioned you: " + chat.latest_message;
                            }
                            
                            let isUpdated = false;

                            // Update text kung nagbago
                            if (sidebarText.textContent.trim() !== displayText.trim()) {
                                sidebarText.textContent = displayText;
                                isUpdated = true;
                            }
                            
                            if (this.selectedUserId != chat.user_id) {
                                if (chat.unread_count > 0) {
                                    totalUnread += chat.unread_count; 
                                    
                                    sidebarItem.classList.add('bg-blue-50/70');
                                    sidebarItem.classList.remove('bg-white', 'hover:bg-gray-50');
                                    sidebarText.classList.add('text-gray-900', 'font-semibold');
                                    sidebarText.classList.remove('text-gray-500');
                                    
                                    if (badgeContainer) {
                                        // Update badge count
                                        if (badgeContainer.innerText != chat.unread_count || badgeContainer.classList.contains('hidden')) {
                                            badgeContainer.innerText = chat.unread_count;
                                            badgeContainer.classList.remove('hidden');
                                            isUpdated = true; // 👈 Pampa-angat kapag dumami ang unread!
                                        }
                                    }
                                } else {
                                    sidebarItem.classList.remove('bg-blue-50/70');
                                    sidebarItem.classList.add('bg-white', 'hover:bg-gray-50');
                                    sidebarText.classList.remove('text-gray-900', 'font-semibold');
                                    sidebarText.classList.add('text-gray-500');
                                    
                                    if (badgeContainer && !badgeContainer.classList.contains('hidden')) {
                                        badgeContainer.classList.add('hidden');
                                        isUpdated = true;
                                    }
                                }
                            }

                            // 👈 DITO AANGAT ANG CHATBOX KUNG MAY NAGBAGO!
                            if (isUpdated) {
                                this.moveToTop(sidebarItem); 
                            }
                        }
                    });

                    const navBadge = document.getElementById('nav-chat-badge');
                    if (navBadge) {
                        if (totalUnread > 0) {
                            navBadge.innerText = totalUnread > 99 ? '99+' : totalUnread;
                            navBadge.classList.remove('hidden');
                        } else {
                            navBadge.classList.add('hidden');
                        }
                    }
                } catch (err) {
                    console.error("Sidebar polling error:", err);
                }
            },

            async dynamicallyMarkRead() {
                if (!this.selectedUserId) return;
                try {
                    await fetch(`/messages/${this.selectedUserId}/mark-as-read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                } catch (err) {}
            },

            async sendMessage(receiverId) {
                if (!this.messageInput.trim()) return;
                
                const text = this.messageInput;
                this.messageInput = ''; 
                this.thisUserIsTyping = true; 

                const chatMessages = document.getElementById('chat-messages');
                const tempId = 'temp-msg-' + Date.now();
                
                chatMessages.insertAdjacentHTML('beforeend', `
                    <div id="${tempId}" class="text-right mb-6 w-full relative js-optimistic-my-bubble">
                        <div class="flex flex-col items-end w-full">
                            <span class="inline-block p-3 px-4 rounded-2xl shadow-sm text-sm bg-[#6d0101] text-white rounded-br-none js-msg-text">
                                ${text}
                            </span>
                            <div class="text-[10px] text-gray-400 mt-1">
                                <span>Just now</span>
                            </div>
                        </div>
                        <div class="absolute -bottom-4 right-2 js-realtime-seen-text flex items-center gap-0.5 justify-end"></div>
                    </div>
                `);

                this.$nextTick(() => { 
                    const container = document.getElementById('message-container');
                    if(container) container.scrollTop = container.scrollHeight; 
                });

                try {
                    const response = await fetch('{{ route('messages.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json' 
                        },
                        body: JSON.stringify({
                            receiver_id: receiverId,
                            message: text
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.isSending = false;
                        this.thisUserIsTyping = false; 

                        const sidebarItem = document.getElementById(`sidebar-item-${receiverId}`);
                        if (!sidebarItem) {
                            // Kung wala pa sa sidebar (new chat), mag-reload nang mabilis para ma-generate ng PHP ang bagong chat item.
                            window.location.reload();
                            return; // Stop execution here
                        }

                        const tempBubble = document.getElementById(tempId);
                        if (tempBubble) {
                            // Siguraduhing walang naiwang duplicate na galing sa polling
                            const alreadyExists = document.getElementById('msg-bubble-' + data.current_msg.id);
                            if (alreadyExists && alreadyExists !== tempBubble) {
                                tempBubble.remove();
                            } else {
                                tempBubble.id = 'msg-bubble-' + data.current_msg.id;
                                tempBubble.classList.remove('js-optimistic-my-bubble');

                                const textSpan = tempBubble.querySelector('.js-msg-text');
                                if (textSpan) textSpan.innerHTML = data.current_msg.formatted_content || data.current_msg.content;
                            }
                        }
                        
                        if (data.trigger_ai) {
                            this.otherUserIsTyping = true;
                            
                            fetch(`/messages/${data.current_msg.id}/trigger-ai`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            }).then(() => {
                                this.otherUserIsTyping = false;
                            }).catch(() => {
                                this.otherUserIsTyping = false;
                            });
                        } else {
                            this.otherUserIsTyping = false;
                        }
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
            }
        }));
    });
</script>
@endsection