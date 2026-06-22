@extends('layouts.navigation')

@section('title', 'Archived Teachers')

@section('content')
<div class="flex-1 p-8 bg-white min-h-screen" x-data="{ restoreModal: false, restoreUrl: '' }">
    
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-4xl font-black text-black uppercase tracking-tight">List of Accounts</h2>
                <h3 class="text-2xl font-bold text-gray-500 mt-1 italic">Archived Teachers</h3>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('teacher.list') }}" class="bg-gray-800 hover:bg-black text-white px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 border-2 border-black">
                    <i class="fa-solid fa-arrow-left"></i> Back to Active List
                </a>
            </div>
        </div>

        <div class="overflow-hidden border-2 border-black rounded-lg shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-200 border-b-2 border-black">
                        <th class="px-4 py-4 border-r-2 border-black text-center font-bold text-xl w-24">No.</th>
                        <th class="px-6 py-4 border-r-2 border-black font-bold text-xl">Name</th>
                        <th class="px-6 py-4 font-bold text-xl">Advisory Class</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($archivedTeachers as $index => $teacherUser)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-4 border-r-2 border-black text-center font-bold text-lg text-gray-700">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 border-r-2 border-black font-bold text-lg uppercase text-gray-500">
                            {{ $teacherUser->teacher?->first_name ?? 'NO PROFILE' }} {{ $teacherUser->teacher?->last_name ?? '' }}
                        </td>
                        <td class="px-6 py-4 flex justify-between items-center text-gray-500">
                            <span class="font-bold text-lg">
                                {{ $teacherUser->teacher?->section?->section_name ?? 'No Advisory' }}
                            </span>
                            <div class="flex gap-2 items-center">
                                
                                <button type="button" 
                                    @click="restoreModal = true; restoreUrl = '{{ route('account.teacher.restore', $teacherUser->user_id) }}'" 
                                    title="Restore Account" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-full font-bold text-sm transition-colors flex items-center gap-2">
                                    <i class="fa-solid fa-arrow-rotate-left"></i> Restore
                                </button>
                                
                                <form action="{{ route('account.teacher.destroy', $teacherUser->user_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this account?');">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" title="Delete Permanently" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-full font-bold text-sm transition-colors"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-6 py-10 text-center text-gray-500 font-bold text-xl">No archived teachers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="restoreModal" 
         x-transition:opacity
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
         x-cloak>
        <div class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative" 
             @click.away="restoreModal = false">
            <button @click="restoreModal = false" class="absolute top-4 right-6 text-4xl font-black text-gray-400 hover:text-black transition-colors">&times;</button>
            <div class="text-center mt-4">
                <i class="fa-solid fa-arrow-rotate-left text-6xl text-blue-500 mb-6 drop-shadow-md"></i>
                <h2 class="text-3xl font-black mb-4 uppercase tracking-tight">Restore Account?</h2>
                <p class="text-lg font-bold text-gray-600 mb-8 leading-tight">
                    Are you sure you want to restore this teacher account? They will reappear on the active list.
                </p>
                <div class="flex flex-col gap-3">
                    <form :action="restoreUrl" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full bg-blue-500 text-white font-black py-4 rounded-xl border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-600 active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all uppercase text-xl">
                            YES, RESTORE
                        </button>
                    </form>
                    <button @click="restoreModal = false" type="button" class="w-full bg-gray-100 text-gray-700 font-black py-4 rounded-xl border-[3px] border-black hover:bg-gray-200 transition-all uppercase text-lg">
                        CANCEL
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection