@extends('layouts.navigation')

@section('title', 'Archived Parents')

@section('content')
<div class="flex-1 p-8 bg-gray-100 min-h-screen" x-data="{ restoreModal: false, restoreUrl: '' }">
    <div class="max-w-6xl mx-auto">
        
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-4xl font-black text-black uppercase tracking-tight">List of Accounts</h2>
                <h3 class="text-2xl font-bold text-gray-500 mt-1 italic">Archived Parents/Students</h3>
            </div>
            
            <div class="flex gap-4">
                <a href="{{ route('parent.list') }}" class="bg-white hover:bg-gray-50 text-black px-6 py-3 rounded-xl font-bold transition flex items-center gap-2 border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none uppercase tracking-wider">
                    <i class="fa-solid fa-arrow-left"></i> Back to Accounts
                </a>
            </div>
        </div>

        <div class="border-[3px] border-black rounded-2xl overflow-hidden bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
            <table class="w-full text-left border-collapse min-w-max">
                <thead class="bg-[#f59e0b] border-b-[3px] border-black text-xl font-bold text-black uppercase">
                    <tr>
                        <th class="p-4 border-r-[3px] border-black text-center w-24 font-black">No.</th>
                        <th class="p-4 border-r-[3px] border-black w-40 font-black text-center">LRN</th>
                        <th class="p-4 border-r-[3px] border-black font-black">Learner</th>
                        <th class="p-4 border-r-[3px] border-black font-black text-center">Previous Grade/Section</th>
                        <th class="p-4 text-center font-black">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y-[3px] divide-black">
                    @forelse($archivedStudents as $index => $student)
                        <tr class="hover:bg-yellow-50 transition text-gray-800">
                            <td class="p-4 border-r-[3px] border-black text-center font-bold">{{ $index + 1 }}</td>
                            <td class="p-4 border-r-[3px] border-black font-bold text-center">{{ $student->lrn }}</td>
                            <td class="p-4 border-r-[3px] border-black font-bold uppercase">{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td class="p-4 border-r-[3px] border-black font-bold text-center">{{ $student->grade_level }} - {{ $student->section->section_name ?? 'N/A' }}</td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-2 items-center">
                                    <button type="button" 
                                        @click="restoreModal = true; restoreUrl = '{{ route('account.parent.restore', $student->user_id) }}'" 
                                        title="Restore Account" 
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl font-bold text-sm transition-all flex items-center gap-2 border-[3px] border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none uppercase tracking-wider">
                                        <i class="fa-solid fa-arrow-rotate-left"></i> Restore
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500 font-bold text-xl uppercase italic">No archived parents found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="restoreModal" 
         x-transition:opacity
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" 
         x-cloak>
        <div class="bg-white border-[4px] border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative" 
             @click.away="restoreModal = false">
            
            <button @click="restoreModal = false" class="absolute top-4 right-6 text-4xl font-black text-gray-400 hover:text-black transition-colors">&times;</button>
             
            <div class="text-center mt-4">
                <i class="fa-solid fa-arrow-rotate-left text-6xl text-blue-500 mb-6 drop-shadow-md"></i>
                <h2 class="text-3xl font-black mb-4 uppercase tracking-tight">Restore Account?</h2>
                <p class="text-lg font-bold text-gray-600 mb-8 leading-tight">
                    Are you sure you want to restore this parent account? The student will reappear on the active list.
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