@extends('layouts.navigation')

@section('title', 'Archives - Mendoza Academy')

@section('content')
    <div class="p-8 bg-gray-100 min-h-screen">
        
        <!-- Header & Back Button -->
        <div class="flex items-center mb-8">
            <a href="{{ route('dashboard') }}" class="flex items-center mr-8 text-gray-700 hover:text-red-600 transition group">
                <div class="bg-white p-2 rounded-full shadow-sm group-hover:bg-red-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </div>
            </a>

            <div class="flex items-center">
                <div class="text-4xl mr-3">📦</div> 
                <h1 class="text-4xl font-black tracking-tight text-black">Archives</h1>
            </div>
        </div>

        <!-- Archives List -->
        <div class="bg-white rounded-3xl shadow-xl p-8 max-w-4xl">
            <div class="space-y-6">
                @forelse($archivedImages ?? [] as $image)
                    <div class="flex items-center justify-between group">
                        <div class="flex items-center space-x-6">
                            <div class="relative w-16 h-16 rounded-xl overflow-hidden shadow-md border-2 border-white">
                                <img src="{{ asset('storage/' . ($image->image_path ?? '')) }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-blue-500 opacity-10"></div>
                            </div>

                            <div>
                                <h3 class="text-xl font-bold text-gray-800 tracking-wide">
                                    {{ basename($image->image_path ?? '') }}
                                </h3>
                                <p class="text-xs text-gray-400 uppercase font-bold">
                                    Archived on {{ \Carbon\Carbon::parse($image->updated_at)->format('M d, Y') }}
                                </p>
                            </div>
                        </div>

                        <form action="{{ route('announcement-images.restore', $image->image_id ?? $image->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="opacity-0 group-hover:opacity-100 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full font-bold text-xs uppercase tracking-widest transition-all shadow-lg shadow-green-200">
                                Restore Image
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <p class="text-gray-400 italic">The archive is currently empty.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
@endsection