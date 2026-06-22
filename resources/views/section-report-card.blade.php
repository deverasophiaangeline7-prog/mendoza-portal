@extends('layouts.navigation')

@section('title', 'Student List')

@section('content')
<main class="flex-1 p-8 bg-white min-h-screen">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-8 border-b-4 border-black pb-4">
            <div>
                <h2 class="text-4xl font-black text-black uppercase tracking-tight">Student List</h2>
                <h3 class="text-2xl font-bold text-orange-500 uppercase">{{ $sectionName }}</h3>
            </div>
            <a href="{{ route('reportcard.index') }}" class="text-red-600 text-5xl hover:scale-110 transition leading-none">
                <i class="fa-solid fa-circle-left"></i>
            </a>
        </div>

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
                                   class="bg-[#ffaf2e] hover:bg-orange-500 text-black px-8 py-2 rounded-xl font-black border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all inline-block uppercase tracking-wider">
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