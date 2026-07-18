@extends('layouts.navigation')

@section('content')
<!-- Main Wrapper: Flex layout to place sidebar and chat side-by-side -->
<div class="h-[calc(100vh-100px)] w-full bg-white overflow-hidden flex">
    
    <!-- ================== -->
    <!-- 1. CHATS SIDEBAR   -->
    <!-- ================== -->
    <div class="w-80 border-r flex flex-col bg-white flex-shrink-0">
        
        <!-- Sidebar Header -->
        <div class="p-4 font-bold text-lg border-b bg-gray-50 flex justify-between items-center">
            <span>Chats</span>
            <button class="text-white bg-[#6d0101] hover:bg-red-900 rounded-full w-8 h-8 flex items-center justify-center transition">
                <i class="fa-solid fa-plus text-sm"></i>
            </button>
        </div>
        
        <!-- Sidebar User List -->
        <div class="overflow-y-auto flex-1">
            @foreach($users as $user)
            <!-- Ensure everything stays inside this anchor tag -->
            <a href="{{ route('messages.show', ['id' => $user->id]) }}" 
               class="block p-4 border-b border-gray-300 hover:bg-gray-100 transition {{ (isset($selectedUser) && $selectedUser->id == $user->id) ? 'bg-gray-50 border-l-4 border-[#6d0101]' : 'border-l-4 border-transparent' }}">
                <div class="flex items-center">
                    
                    <!-- Avatar -->
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" class="w-12 h-12 rounded-full mr-3 border" alt="User">
                    
                    <!-- Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline">
                            <!-- Name and Icons perfectly aligned -->
                            <span class="font-bold text-gray-900 truncate">
                                @if(isset($user->type) && $user->type === 'announcement') 📌 
                                @elseif(isset($user->type) && $user->type === 'advisory') 🎓 
                                @endif    
                                {{ $user->name }}
                            </span>
                            <span class="text-xs text-gray-600 ml-2">03/19</span>
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
                        <h3 class="font-bold text-gray-800">{{ $selectedUser->name }}</h3>
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
                        <div class="{{ $message->sender_id === auth()->id() ? 'text-right' : 'text-left' }}">
                            <span class="inline-block p-3 px-4 rounded-2xl shadow-sm text-sm {{ $message->sender_id === auth()->id() ? 'bg-[#6d0101] text-white rounded-br-none' : 'bg-gray-100 text-gray-800 rounded-bl-none' }}">
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
                    <!-- Normal Input Box: Shows for Direct Messages AND for the Adviser in the Advisory Class -->
                    <form action="{{ route('messages.store') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $selectedUser->id }}">
                        <input type="text" name="message" class="flex-1 border border-gray-300 rounded-full px-5 py-3 focus:outline-none focus:border-[#6d0101] focus:ring-1 focus:ring-[#6d0101] transition-all" placeholder="Type your message here..." required>
                        <button type="submit" class="bg-[#6d0101] text-white px-6 py-2 rounded-full hover:bg-red-900 transition">
                            Send
                        </button>
                    </form>
                @endif
            </div>
        
        @else
            <!-- Empty State (Shows when no user is clicked) -->
            <div class="flex-1 flex flex-col items-center justify-center text-gray-400 bg-gray-50">
                <i class="fa-solid fa-comment-dots text-6xl mb-4 text-gray-300"></i>
                <p class="text-lg font-semibold text-gray-500">Click a message to view</p>
            </div>
        @endisset

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