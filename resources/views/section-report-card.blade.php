@extends('layouts.navigation')

@section('title', 'Student List')

@section('content')
<main class="flex-1 p-8 bg-white min-h-screen">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-8 border-b-4 border-black pb-4">
            <div>
                <h2 class="text-4xl font-black text-black uppercase tracking-tight">Student List</h2>
                <h3 class="text-2xl font-bold text-amber-700 uppercase">{{ $sectionName }}</h3>
            </div>
            <a href="{{ route('reportcard.index') }}" class="text-red-600 text-5xl hover:scale-110 transition leading-none">
                <i class="fa-solid fa-circle-left"></i>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border-[3px] border-black bg-green-100 px-4 py-3 font-black text-green-800 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-xl border-[3px] border-black bg-red-100 px-4 py-3 font-black text-red-800 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                {{ session('error') }}
            </div>
        @endif

        @if(auth()->user()->role === 'teacher')
            @php
                $teacherData = \App\Models\Teacher::where('user_id', auth()->id())->first();
                $assignedSubj = $teacherData ? $teacherData->assigned_subject : 'NONE';
            @endphp
            
            <div class="mb-4 inline-flex items-center gap-3 bg-blue-50 border-[3px] border-black px-4 py-2 rounded-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <i class="fa-solid fa-shield-halved text-blue-600 text-xl"></i>
                <span class="font-bold text-gray-800">
                     You are assigned to grade <span class="text-red-600 font-black uppercase underline">{{ $assignedSubj }}</span>
                </span>
            </div>
        @endif

        {{-- NEW EXCEL SUBJECT IMPORT FORM --}}
        @if(auth()->user()->role === 'teacher')
        <form action="{{ route('batch.import', $section_id) }}" method="POST" enctype="multipart/form-data" class="mb-6 flex flex-wrap items-center justify-end gap-3">
            @csrf
            
            <select name="subject" required class="border-[3px] border-black rounded-xl px-3 py-2 font-black text-black bg-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <option value="">SELECT SUBJECT</option>
                <option value="Filipino">Filipino</option>
                <option value="English">English</option>
                <option value="Mathematics">Mathematics</option>
                <option value="Science">Science</option>
                <option value="Araling Panlipunan (AP)">Araling Panlipunan (AP)</option>
                <option value="GMRC">GMRC</option>
                <option value="EPP / TLE">EPP / TLE</option>
                <option value="Music and Arts">Music and Arts</option>
                <option value="Physical Education and Health">Physical Education and Health</option>
            </select>
            
            <div x-data="{ fileName: '' }">
                <label :class="fileName ? 'bg-red-500 text-white' : 'bg-white text-black'" 
                    class="cursor-pointer border-[3px] border-black rounded-xl px-3 py-2 font-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-colors inline-flex items-center">
                    
                    <i class="fa-solid fa-file-excel mr-2" :class="fileName ? 'text-white' : 'text-black'"></i>
                    
                    <span class="mr-2" x-text="fileName ? fileName : 'Choose XLSX'"></span>
                    
                    <input type="file" name="excel_file" accept=".xlsx, .xls" required class="hidden" 
                        @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                </label>
            </div>

            <button type="submit" class="bg-[#b26905] text-black font-black px-4 py-2 border-[3px] border-black rounded shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all">
                IMPORT GRADES
            </button>
        </form>
        @endif

        <div class="border-[3px] border-black rounded-xl overflow-hidden shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] bg-white">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b-[3px] border-black text-black">
                        <th class="p-4 border-r-[3px] border-black w-24 text-center font-black text-2xl">NO.</th>
                        <th class="p-4 px-6 uppercase font-black text-2xl">Learner's Name</th>
                        <th class="w-48 text-center"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(['Male', 'Female'] as $gender)
                        <tr class="bg-gray-200 border-b-[3px] border-black text-black">
                            <td class="p-3 px-6 font-black text-xl border-r-[3px] border-black italic tracking-widest uppercase" colspan="3">{{ $gender }}</td>
                        </tr>
                        @php $count = 1; @endphp
                        @foreach($students->where('gender', $gender) as $student)
                        <tr class="border-b-[2px] border-black last:border-b-0 hover:bg-yellow-50 transition-colors text-black">
                            <td class="p-4 text-center font-bold text-xl border-r-[3px] border-black text-gray-500">{{ $count++ }}</td>
                            <td class="p-4 px-6 font-black text-2xl uppercase">{{ $student->last_name }}, {{ $student->first_name }}</td>
                            <td class="p-4 text-center">
                                <a href="{{ route('reportcard.showStudent', $student->student_id) }}" 
                                   class="bg-[#b26905] hover:bg-amber-700 text-black px-8 py-2 rounded-xl font-black border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all inline-block uppercase tracking-wider">
                                    VIEW
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</main>
@endsection