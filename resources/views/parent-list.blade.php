@extends('layouts.navigation')

@section('title', 'Parent Accounts')

@section('content')
<main class="flex-1 p-8 bg-white min-h-screen">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-4xl font-black text-black uppercase tracking-tight">List of Accounts</h2>
                <h3 class="text-4xl font-bold text-amber-700 mt-1 italic">Parents</h3>
            </div>
            <a href="{{ route('account.management') }}" class="bg-gray-800 hover:bg-black text-white px-6 py-2 rounded-lg font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-8">
            @php
                $grades = [
                    ['id' => 'nursery',     'level' => 'NURSERY',     'name' => 'St. Mary'],
                    ['id' => 'kinder',      'level' => 'KINDER',      'name' => 'St. Bridget'],
                    ['id' => 'preparatory', 'level' => 'PREPARATORY', 'name' => 'St. Augustine'],
                    ['id' => 'grade-1',     'level' => 'GRADE 1',     'name' => 'Faith'],
                    ['id' => 'grade-2',     'level' => 'GRADE 2',     'name' => 'Hope'],
                    ['id' => 'grade-3',     'level' => 'GRADE 3',     'name' => 'Love'],
                    ['id' => 'grade-4',     'level' => 'GRADE 4',     'name' => 'Grace'],
                    ['id' => 'grade-5',     'level' => 'GRADE 5',     'name' => 'Light'],
                    ['id' => 'grade-6',     'level' => 'GRADE 6',     'name' => 'Wisdom'],
                ];
            @endphp

            @foreach($grades as $grade)
                <button type="button" 
                    onclick="window.location.href='{{ route('grade.show', ['grade' => $grade['id']]) }}'"
                    class="bg-[#ffb31a] border-2 border-black rounded-[40px] py-6 flex flex-col items-center group transition-all active:scale-95">
                    
                    <span class="text-4xl font-black text-black group-hover:-translate-y-1 group-hover:text-amber-700 transition-transform" 
                        style="-webkit-text-stroke: 1.5px white;">
                        {{ $grade['level'] }}
                    </span>
                    <span class="text-xl font-medium text-black group-hover:-translate-y-1 transition-transform">
                        {{ $grade['name'] }}
                    </span>
                </button>
            @endforeach
        </div>
        
    </div>
</main>
@endsection