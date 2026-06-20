@extends('layouts.navigation') <!-- nde ko alam san to -->

@section('title', 'Archived Report Cards')

@section('content')
<div class="bg-gray-100 min-h-screen overflow-y-auto p-8 relative pb-32 w-full">
    <div class="max-w-5xl mx-auto mt-4">
        
        <div class="flex justify-between items-center mb-8">
            <a href="{{ route('account.management') }}" class="bg-white border-[3px] border-black text-black font-bold py-3 px-6 rounded-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-gray-50 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Accounts
            </a>
            
            <div class="text-right">
                <h1 class="text-4xl font-black text-black uppercase tracking-tighter">Archived Records</h1>
                <h2 class="text-2xl font-bold text-red-600">SY {{ $schoolYear->school_year }}</h2>
            </div>
        </div>

        <div class="bg-white border-[4px] border-black rounded-3xl p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]" x-data="{ search: '' }">
            
            <div class="mb-6 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-4 text-gray-400 text-xl"></i>
                <input type="text" x-model="search" placeholder="Search student by name..." class="w-full border-[3px] border-black rounded-xl pl-12 pr-4 py-3 font-bold text-lg focus:outline-none focus:ring-4 focus:ring-yellow-400 transition-all">
            </div>

            <div class="overflow-x-auto border-[3px] border-black rounded-xl">
                <table class="w-full text-left border-collapse min-w-max">
                    <thead>
                        <tr class="bg-[#ffb72b] border-b-[3px] border-black">
                            <th class="p-4 font-black uppercase text-lg border-r-[3px] border-black">LRN</th>
                            <th class="p-4 font-black uppercase text-lg border-r-[3px] border-black">Student Name</th>
                            <th class="p-4 font-black uppercase text-lg border-r-[3px] border-black">Prev Section</th>
                            <th class="p-4 font-black uppercase text-lg text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr class="border-b-[3px] border-black last:border-0 hover:bg-yellow-50 transition-colors" x-show="search === '' || '{{ strtolower($student->last_name . ' ' . $student->first_name) }}'.includes(search.toLowerCase())">
                                <td class="p-4 font-bold border-r-[3px] border-black">{{ $student->lrn }}</td>
                                <td class="p-4 font-bold border-r-[3px] border-black uppercase">{{ $student->last_name }}, {{ $student->first_name }}</td>
                                <td class="p-4 font-bold border-r-[3px] border-black">
                                    {{ isset($histories[$student->student_id]) ? $histories[$student->student_id]->section_name : 'N/A' }}
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ route('archives.reportcards.showStudent', ['student_id' => $student->student_id, 'school_year_id' => $schoolYear->id]) }}" class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-600 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all inline-block">
                                        View Old Grades
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center font-bold text-gray-500">No grades were recorded during this school year.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection