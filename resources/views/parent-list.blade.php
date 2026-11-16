@extends('layouts.navigation')

@section('title', 'Parent Accounts')

@section('content')
<main class="flex-1 p-8 bg-white min-h-screen" x-data="{ addSectionModal: false, deleteSectionModal: false, changePicModal: false, confirmDeleteModal: false, selectedSectionName: '' }">

    <!-- FIXED TOAST NOTIFICATION -->
    @if(session()->has('toast_status') || session()->has('success') || $errors->any())
    @php
        $isError = $errors->any() || session('toast_status') === 'error';
        $toastMessage = $errors->any() ? $errors->first() : (session('toast_message') ?? session('success'));
    @endphp
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
        class="fixed bottom-10 right-10 z-[999999] flex items-center justify-between gap-6 w-auto rounded-xl border-[3px] border-black {{ $isError ? 'bg-red-100 text-red-900' : 'bg-[#d1fae5] text-[#065f46]' }} px-6 py-4 font-black">
        <div class="flex items-center gap-3">
            @if($isError)
                <i class="fa-solid fa-triangle-exclamation text-2xl shrink-0"></i>
            @else
                <i class="fa-solid fa-circle-check text-2xl shrink-0"></i>
            @endif
            <span class="text-base whitespace-nowrap leading-none">{{ $toastMessage }}</span>
        </div>
        <button @click="show = false" class="text-2xl hover:scale-110 transition ml-2 shrink-0">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    @endif

    <div class="max-w-6xl mx-auto">

        <!-- RESPONSIVE HEADER SECTION -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8 w-full">
            <div>
                <h2 class="text-3xl md:text-4xl font-black text-black uppercase tracking-tight leading-none">List of Accounts</h2>
                <div class="flex items-center gap-4 mt-2">
                    <h3 class="text-2xl md:text-4xl font-bold text-amber-700 italic leading-none">Parents</h3>
                    @if(in_array(auth()->user()->role, ['admin', 'superadmin'])) 
                        <!-- Simple Pill Badge -->
                        <span class="text-xs md:text-sm font-bold text-gray-700 uppercase tracking-wider bg-gray-100 px-3 py-1 rounded-full border border-black/20 shrink-0 whitespace-nowrap not-italic">
                            Total: <span class="text-[#b91c1c] font-black ml-1">{{ count($parents ?? []) }}</span>
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <button @click="changePicModal = true" type="button" class="flex-1 lg:flex-none justify-center bg-blue-600 hover:bg-blue-700 text-white px-3 md:px-5 py-2 rounded-lg font-bold transition flex items-center gap-2 text-sm md:text-base whitespace-nowrap">
                    <i class="fa-solid fa-image"></i> Change Picture
                </button>
                <button @click="addSectionModal = true" type="button" class="flex-1 lg:flex-none justify-center bg-green-600 hover:bg-green-700 text-white px-3 md:px-5 py-2 rounded-lg font-bold transition flex items-center gap-2 text-sm md:text-base whitespace-nowrap">
                    <i class="fa-solid fa-plus"></i> Add Section
                </button>
                <button @click="deleteSectionModal = true" type="button" class="flex-1 lg:flex-none justify-center bg-red-600 hover:bg-red-700 text-white px-3 md:px-5 py-2 rounded-lg font-bold transition flex items-center gap-2 text-sm md:text-base whitespace-nowrap">
                    <i class="fa-solid fa-trash"></i> Delete Section
                </button>
                <a href="{{ route('account.management') }}" class="w-full sm:w-auto flex-1 lg:flex-none justify-center bg-gray-800 hover:bg-black text-white px-4 md:px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 text-sm md:text-base whitespace-nowrap">
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

    {{-- CHANGE PROFILE PICTURE MODAL (Consistent Neo-Brutalist Styling) --}}
    <div x-show="changePicModal" class="fixed inset-0 z-50 flex p-4 bg-black/60 backdrop-blur-sm overflow-y-auto" x-cloak>
        <div @click.away="changePicModal = false" class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] m-auto flex-shrink-0 relative">
            
            <button type="button" @click="changePicModal = false" class="absolute top-4 right-6 text-3xl text-gray-400 hover:text-red-600 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
            
            <h3 class="text-3xl font-black mb-6 uppercase text-black italic">Change Picture</h3>
            
            <form action="{{ route('parent.updatePicture') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-5">
                    <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Select Parent <span class="text-red-600">*</span></label>
                    <select name="parent_id" required class="w-full border-2 border-black rounded-xl p-3 font-bold focus:outline-none focus:ring-4 focus:ring-blue-400 bg-white">
                        <option value="" disabled selected>-- Select Account --</option>
                        @foreach($parents ?? [] as $parent)
                        <option value="{{ $parent->user_id ?? $parent->id }}">
                            {{ $parent->parent?->first_name && $parent->parent?->last_name ? $parent->parent->first_name . ' ' . $parent->parent->last_name : ($parent->name ?? $parent->username) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-8">
                    <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Upload New Picture <span class="text-red-600">*</span></label>
                    <input type="file" name="profile_picture" accept="image/*" required class="w-full border-2 border-black rounded-xl p-2 font-bold focus:outline-none focus:ring-4 focus:ring-blue-400 bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-2 file:border-black file:text-sm file:font-black file:bg-[#ffb31a] file:text-black hover:file:bg-[#e68a2d] file:transition-colors cursor-pointer">
                    <p class="text-[11px] text-gray-500 mt-2 font-bold uppercase tracking-widest">Accepted formats: JPG, PNG. Max size: 2MB.</p>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" @click="changePicModal = false" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl font-bold uppercase border-2 border-black hover:bg-gray-300 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-black uppercase border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-700 active:translate-y-[2px] active:translate-x-[2px] active:shadow-none transition-all flex items-center gap-2">
                        <i class="fa-solid fa-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ADD SECTION MODAL --}}
    <div x-show="addSectionModal" class="fixed inset-0 z-50 flex p-4 bg-black/50 overflow-y-auto" x-cloak>
        <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-xl relative m-auto flex-shrink-0 max-h-[90vh] overflow-y-auto">
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
                <div class="flex justify-end gap-2 mt-4 shrink-0">
                    <button type="button" @click="addSectionModal = false" class="px-4 py-2 bg-gray-200 rounded-lg font-bold">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-bold">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- DELETE SECTION MODAL --}}
    <div x-show="deleteSectionModal" class="fixed inset-0 z-50 flex p-4 bg-black/50 overflow-y-auto" x-cloak>
        <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-xl relative m-auto flex-shrink-0 max-h-[90vh] overflow-y-auto">
            <button @click="deleteSectionModal = false" class="absolute top-4 right-4 text-gray-500 hover:text-black">&times;</button>
            <h3 class="text-2xl font-black mb-4">Delete Section</h3>
            <form id="deleteSectionForm" action="{{ route('sections.destroy') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="mb-4">
                    <label class="block font-bold mb-1">Select Section</label>
                    <select name="section_id" id="sectionSelect" required class="w-full border rounded-lg p-2 font-bold bg-white">
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
                <div class="flex justify-end gap-2 mt-4 shrink-0">
                    <button type="button" @click="deleteSectionModal = false" class="px-4 py-2 bg-gray-200 rounded-lg font-bold">Cancel</button>
                    <button type="button" 
                            @click="
                                const select = document.getElementById('sectionSelect');
                                if(select.value) { 
                                    selectedSectionName = select.options[select.selectedIndex].text.trim();
                                    deleteSectionModal = false; 
                                    confirmDeleteModal = true; 
                                } else { 
                                    document.getElementById('deleteSectionForm').reportValidity(); 
                                }
                            " 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg font-bold">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- CUSTOM CONFIRMATION MODAL (Matching UI Reference) --}}
    <div x-show="confirmDeleteModal" class="fixed inset-0 z-[60] flex p-4 bg-black/50 overflow-y-auto" x-cloak>
        <div class="bg-white border-4 border-[#b91c1c] rounded-3xl p-8 max-w-sm w-full shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] relative m-auto flex-shrink-0 text-center">

            <!-- Layered Red Trash Icon -->
            <div class="relative w-20 h-20 mx-auto mb-4">
                <div class="absolute inset-0 bg-[#b91c1c] rounded-full translate-x-1.5 translate-y-1.5"></div>
                <div class="absolute inset-0 bg-[#fee2e2] rounded-full flex items-center justify-center border-2 border-[#b91c1c]">
                    <i class="fa-solid fa-trash text-3xl text-[#b91c1c]"></i>
                </div>
            </div>

            <h3 class="text-3xl font-black text-[#b91c1c] uppercase italic mb-3 tracking-tighter">Delete Section?</h3>

            <p class="text-gray-600 font-bold mb-8 text-sm uppercase leading-relaxed">
                Are you sure you want to delete <br>
                <span x-text="selectedSectionName" class="text-[#b91c1c] text-base font-black"></span>?<br>
                This action cannot be undone.
            </p>

            <div class="flex justify-center gap-3 shrink-0">
                <button type="button" @click="confirmDeleteModal = false; deleteSectionModal = true" class="flex-1 py-3 bg-[#e5e7eb] border-2 border-black rounded-xl font-black text-black uppercase hover:bg-gray-300 transition">Cancel</button>
                <button type="button" @click="document.getElementById('deleteSectionForm').submit()" class="flex-1 py-3 bg-[#b91c1c] border-2 border-black text-white rounded-xl font-black uppercase hover:bg-red-800 transition">Delete</button>
            </div>
        </div>
    </div>
</main>
@endsection