<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="flex h-screen bg-white">
    <!-- Sidebar: Chat List -->
    <div class="w-1/3 border-r">
        <div class="p-4 font-bold text-lg border-b">Chats</div>
        <div class="overflow-y-auto">
            @foreach($users as $user)
                <div wire:click="selectConversation({{ $user->id }})" 
                     class="p-4 flex items-center cursor-pointer hover:bg-gray-100 {{ $selectedConversationId == $user->id ? 'bg-blue-50' : '' }}">
                    <img src="{{ $user->profile_photo_url }}" class="w-10 h-10 rounded-full mr-3">
                    <div class="flex-1">
                        <div class="flex justify-between">
                            <span class="font-semibold">{{ $user->name }}</span>
                            <span class="text-xs text-gray-500">03/23</span>
                        </div>
                        <p class="text-sm text-gray-600 truncate">Click to view message...</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Main Content: Message View -->
    <div class="w-2/3 flex flex-col justify-center items-center">
        @if($selectedConversationId)
            <div class="w-full p-6 overflow-y-auto flex-1">
                @foreach($messages as $msg)
                    <div class="{{ $msg->sender_id == auth()->id() ? 'text-right' : 'text-left' }} mb-4">
                        <span class="inline-block p-2 rounded-lg {{ $msg->sender_id == auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                            {{ $msg->body }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State from your screenshot -->
            <div class="text-center">
                <svg class="w-20 h-20 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <h2 class="text-2xl font-bold mt-4">Click a message to view</h2>
            </div>
        @endif
    </div>
</div>