@extends('layouts.navigation')

@section('title', 'Parent Accounts')

@section('content')
<main class="flex-1 p-8 bg-white min-h-screen" x-data="{ addSectionModal: false, deleteSectionModal: false }">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-4xl font-black text-black uppercase tracking-tight">List of Accounts</h2>
                <h3 class="text-4xl font-bold text-amber-700 mt-1 italic">Parents</h3>
            </div>
            <div class="flex items-center gap-3">
                <button @click="addSectionModal = true" type="button" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-bold transition flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Add Section
                </button>
                <button @click="deleteSectionModal = true" type="button" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg font-bold transition flex items-center gap-2">
                    <i class="fa-solid fa-trash"></i> Delete Section
                </button>
                <a href="{{ route('account.management') }}" class="bg-gray-800 hover:bg-black text-white px-6 py-2 rounded-lg font-bold transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-8">
            @foreach($sections as $section)
                @php
                    $isNKP = in_array(strtoupper($section->grade_level), ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREPARATORY', 'PREP', 'NKP']);
                    $displayLevel = $isNKP ? strtoupper($section->grade_level) : (stripos($section->grade_level, 'GRADE') !== false ? strtoupper($section->grade_level) : 'GRADE ' . $section->grade_level);
                @endphp
                <button type="button" 
                    onclick="window.location.href='{{ route('grade.show', ['grade' => $section->section_id]) }}'"
                    class="bg-[#e68a2d] border-2 border-black rounded-[40px] py-6 flex flex-col items-center group transition-all active:scale-95 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] relative">
                    
                    <span class="text-4xl font-black text-black group-hover:-translate-y-1 group-hover:text-amber-700 transition-transform uppercase" 
                        style="-webkit-text-stroke: 1.5px white;">
                        {{ $displayLevel }}
                    </span>
                    <span class="text-xl font-medium text-black group-hover:-translate-y-1 transition-transform">
                        {{ $section->section_name }}
                    </span>
                </button>
            @endforeach
        </div>
        
    </div>

    {{-- ADD SECTION MODAL --}}
    <div x-show="addSectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak>
        <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-xl relative">
            <button @click="addSectionModal = false" class="absolute top-4 right-4 text-gray-500 hover:text-black">&times;</button>
            <h3 class="text-2xl font-black mb-4">Add Section</h3>
            <form action="{{ route('sections.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block font-bold mb-1">Grade Level</label>
                    <select name="grade_level" required class="w-full border rounded-lg p-2 font-bold bg-white">
                        <option value="" disabled selected>-- Select Grade Level --</option>
                        <option value="Nursery">Nursery</option>
                        <option value="Kindergarten">Kindergarten</option>
                        <option value="Preparatory">Preparatory</option>
                        <option value="1">Grade 1</option>
                        <option value="2">Grade 2</option>
                        <option value="3">Grade 3</option>
                        <option value="4">Grade 4</option>
                        <option value="5">Grade 5</option>
                        <option value="6">Grade 6</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block font-bold mb-1">Section Name</label>
                    <input type="text" name="section_name" required class="w-full border rounded-lg p-2 font-bold" oninput="this.value = this.value.toLowerCase().replace(/\b\w/g, c => c.toUpperCase())">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="addSectionModal = false" class="px-4 py-2 bg-gray-200 rounded-lg font-bold">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-bold">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- DELETE SECTION MODAL --}}
    <div x-show="deleteSectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak>
        <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-xl relative">
            <button @click="deleteSectionModal = false" class="absolute top-4 right-4 text-gray-500 hover:text-black">&times;</button>
            <h3 class="text-2xl font-black mb-4">Delete Section</h3>
            <form action="{{ route('sections.destroy') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this section?');">
                @csrf
                @method('DELETE')
                <div class="mb-4">
                    <label class="block font-bold mb-1">Select Section</label>
                    <select name="section_id" required class="w-full border rounded-lg p-2 font-bold bg-white">
                        <option value="" disabled selected>-- Select Section --</option>
                        @foreach($sections ?? [] as $sec)
                            <option value="{{ $sec->section_id }}">
                                @if(is_numeric($sec->grade_level))
                                    Grade {{ $sec->grade_level }}
                                @else
                                    {{ $sec->grade_level }}
                                @endif
                                - {{ $sec->section_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="deleteSectionModal = false" class="px-4 py-2 bg-gray-200 rounded-lg font-bold">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg font-bold">Delete</button>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection