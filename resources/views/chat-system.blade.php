@extends('layouts.navigation')

@section('title', 'Messages - Mendoza Academy')

@section('content')
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
    
    <div class="w-80 border-r flex flex-col bg-white flex-shrink-0">
        <div class="p-4 font-bold text-lg border-b bg-gray-50 flex justify-between items-center relative" x-data="{ searchOpen: false, searchQuery: '' }">
            <div class="flex items-center flex-1 mr-2 relative">
                <span x-show="!searchOpen" class="text-gray-800">Chats</span>
                <div x-show="searchOpen" class="w-full flex items-center" style="display: none;">
                    <input type="text" x-model="searchQuery" placeholder="Search user name..." class="w-full text-sm border border-gray-300 rounded-full px-3 py-1.5 focus:outline-none focus:border-[#6d0101] focus:ring-1 focus:ring-[#6d0101] bg-white">
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <button @click="searchOpen = !searchOpen" class="text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-full w-8 h-8 flex items-center justify-center transition">
                    <i class="fa-solid text-sm" :class="searchOpen ? 'fa-xmark' : 'fa-magnifying-glass'"></i>
                </button>
                
                <div x-data="{ dropdownOpen: false }" class="relative">
                    <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false" class="text-white bg-[#6d0101] hover:bg-red-900 rounded-full w-8 h-8 flex items-center justify-center transition">
                        <i class="fa-solid fa-plus text-sm"></i>
                    </button>

                    <div x-show="dropdownOpen" style="display: none;" class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden">
                        <button @click="dropdownOpen = false; newMsgModal = true" class="w-full text-left block px-4 py-3 text-sm text-gray-800 font-bold hover:bg-gray-100 border-b border-gray-100 transition-colors">
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
                <a href="{{ route('messages.show', ['id' => $user->user_id]) }}" class="block p-4 border-b border-gray-200 transition {{ $hasUnread ? 'bg-blue-50/70' : 'bg-white hover:bg-gray-50' }} {{ (isset($selectedUser) && $selectedUser->user_id == $user->user_id) ? 'border-l-4 border-[#6d0101] bg-gray-50' : 'border-l-4 border-transparent' }}">
                    <div class="flex items-center">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" class="w-12 h-12 rounded-full mr-3 border flex-shrink-0" alt="User">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline">
                                <span class="truncate flex items-center gap-1 min-w-0 {{ $hasUnread ? 'font-bold text-black' : 'font-bold text-gray-900' }}">
                                    @if(isset($user->type) && $user->type === 'announcement') 📌 
                                    @elseif(isset($user->type) && $user->type === 'advisory') 🎓 
                                    @endif    
                                    <span class="truncate">{{ $user->name }}</span>
                                    @if($hasUnread)
                                        <span class="bg-red-600 text-white rounded-full px-2 py-0.5 text-[10px] font-bold ml-1 flex-shrink-0">{{ $user->unreadMessagesCount() }}</span>
                                    @endif
                                </span>
                            </div>
                            <p class="text-xs truncate mt-0.5 {{ $hasUnread ? 'text-gray-900 font-semibold' : 'text-gray-500' }}">
                                {{ $latestMsg ? $latestMsg->content : 'No messages yet...' }}
                            </p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-gray-500 text-sm">No active chats. Click the + icon to start a new conversation.</div>
            @endforelse
        </div>
    </div>

    <div class="flex-1 flex flex-col bg-white overflow-hidden">
        @isset($selectedUser)
            <div class="p-4 border-b bg-white flex items-center justify-between shadow-sm flex-shrink-0">
                <div class="flex items-center">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($selectedUser->name) }}" class="w-10 h-10 rounded-full mr-3 border" alt="User">
                    <div>
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            {{ $selectedUser->name }}
                        </h3>
                        @if($selectedUser->isOnline())
                            <span class="text-xs text-green-500 flex items-center mt-0.5"><span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span> Active Now</span>
                        @else
                            <span class="text-xs text-gray-400 flex items-center mt-0.5"><span class="w-2 h-2 bg-gray-400 rounded-full mr-1"></span> Offline</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div id="message-container" class="flex-1 overflow-y-auto p-4 space-y-4">
                @if(isset($messages) && count($messages) > 0)
                    @foreach($messages as $message)
                        <div class="{{ $message->sender_id === auth()->user()->user_id ? 'text-right' : 'text-left' }}">
                            <span class="inline-block p-3 px-4 rounded-2xl shadow-sm text-sm {{ $message->sender_id === auth()->user()->user_id ? 'bg-[#6d0101] text-white rounded-br-none' : 'bg-gray-100 text-gray-800 rounded-bl-none' }}">
                                {{ $message->content }}
                            </span>
                            <div class="text-[10px] text-gray-400 mt-1">{{ $message->created_at->format('g:i A') }}</div>
                        </div>
                    @endforeach
                @else
                    <div class="h-full flex flex-col items-center justify-center text-gray-400">
                        <p class="text-sm">No messages yet. Send a message to start the conversation.</p>
                    </div>
                @endif
                
                <!-- Live Typing Bubble -->
                <div class="text-left" x-show="isTyping" x-cloak>
                    <div class="typing-indicator shadow-sm">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t bg-white flex-shrink-0">
                @php $isAdviser = false; @endphp
                @if(isset($selectedUser->type) && $selectedUser->type === 'announcement')
                    <div class="text-center text-sm text-gray-500 py-3 bg-gray-50 rounded-full border border-gray-200">
                        <i class="fa-solid fa-lock mr-1"></i> Only administrators can send messages.
                    </div>
                @else
                    <form @submit.prevent="sendMessage('{{ $selectedUser->user_id }}')" class="flex gap-2">
                        <input type="text" 
                               x-model="messageInput" 
                               @input="sendTyping()"
                               :disabled="isTyping" 
                               class="flex-1 border border-gray-300 rounded-full px-5 py-3 focus:outline-none focus:border-[#6d0101] focus:ring-1 focus:ring-[#6d0101] transition-all disabled:opacity-50" 
                               placeholder="Type your message here..." 
                               required>
                        <button type="submit" :disabled="isTyping" class="bg-[#6d0101] text-white px-6 py-2 rounded-full hover:bg-red-900 transition disabled:opacity-50">
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
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('chatSystem', () => ({
            newMsgModal: false, 
            createGroupModal: false,
            messageInput: '',
            isTyping: false,
            myId: '{{ auth()->user()->user_id }}',
            selectedUserId: '{{ isset($selectedUser) ? $selectedUser->user_id : "" }}',
            typingTimer: null,

            init() {
                const container = document.getElementById('message-container');
                if (container) container.scrollTop = container.scrollHeight;

                // Listen for human-to-human typing via Pusher
                if (window.Echo && this.myId && this.selectedUserId) {
                    window.Echo.private(`chat.${this.myId}`)
                        .listenForWhisper('typing', (e) => {
                            if (e.senderId == this.selectedUserId) {
                                this.isTyping = true;
                                if (container) container.scrollTop = container.scrollHeight;

                                clearTimeout(this.typingTimer);
                                this.typingTimer = setTimeout(() => {
                                    this.isTyping = false;
                                }, 2000);
                            }
                        });
                }
            },

            sendTyping() {
                if (window.Echo && this.selectedUserId) {
                    window.Echo.private(`chat.${this.selectedUserId}`)
                        .whisper('typing', {
                            senderId: this.myId
                        });
                }
            },

            async sendMessage(receiverId) {
                if (!this.messageInput.trim()) return;
                
                const text = this.messageInput;
                this.messageInput = ''; 
                
                const container = document.getElementById('message-container');
                container.insertAdjacentHTML('beforeend', `
                    <div class="text-right mb-4">
                        <span class="inline-block p-3 px-4 rounded-2xl shadow-sm text-sm bg-[#6d0101] text-white rounded-br-none">
                            ${text}
                        </span>
                    </div>
                `);
                
                this.isTyping = true;
                this.$nextTick(() => { container.scrollTop = container.scrollHeight; });

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
                        window.location.reload(); 
                    }
                } catch (err) {
                    this.isTyping = false;
                    console.error("Message failed to send", err);
                }
            }
        }));
    });
</script>
@endsection