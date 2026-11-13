@extends('layouts.navigation')

@section('title', 'Parent Accounts')

@section('content')
<main class="flex-1 p-8 bg-white min-h-screen" x-data="{ addSectionModal: false, deleteSectionModal: false, changePicModal: false, confirmDeleteModal: false, selectedSectionName: '' }">
    
    <!-- FIXED TOAST NOTIFICATION -->
    @if(session()->has('toast_status') || $errors->any() || session('success'))
        @php
            $isError = $errors->any() || session('toast_status') === 'error';
            $toastMessage = $errors->any() ? $errors->first() : (session('toast_message') ?? session('success'));
        @endphp
        
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-10"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-10"
             class="fixed bottom-10 right-10 z-[999999] flex items-center justify-between gap-6 min-w-[320px] rounded-xl border-4 border-black {{ $isError ? 'bg-red-100 text-red-900' : 'bg-[#4ade80] text-black' }} px-6 py-4 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
            
            <div class="flex items-center gap-3">
                @if($isError)
                    <i class="fa-solid fa-circle-exclamation text-2xl shrink-0"></i>
                @else
                    <i class="fa-solid fa-circle-check text-2xl shrink-0"></i>
                @endif
                <span class="text-lg font-black leading-tight">{{ $toastMessage }}</span>
            </div>
            
            <button @click="show = false" class="text-2xl hover:scale-110 transition shrink-0 font-black">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif
    <!-- END TOAST -->

    <div class="max-w-6xl mx-auto">
       
        <!-- RESPONSIVE HEADER SECTION -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8 w-full">
            <div>
                <h2 class="text-3xl md:text-4xl font-black text-black uppercase tracking-tight leading-none">List of Accounts</h2>
                <div class="flex items-center gap-4 mt-2">
                    <h3 class="text-2xl md:text-4xl font-bold text-amber-700 italic leading-none">Parents</h3>
                    @if(auth()->user()->role === 'admin')    
                        <!-- Redesigned Neobrutalist Split-Badge -->
                        <div class="flex items-stretch border-[3px] border-black rounded-xl overflow-hidden shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] not-italic h-8 md:h-10">
                            <div class="bg-black text-white flex items-center justify-center px-3 md:px-4 text-xs md:text-sm font-black uppercase tracking-widest">
                                Total
                            </div>
                            <div class="bg-white flex items-center justify-center px-3 md:px-4 text-[#b91c1c] font-black text-sm md:text-base border-l-[3px] border-black">
                                {{ \App\Models\User::where('role', 'parent')->count() }}
                            </div>
                        </div>
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

    {{-- CHANGE PROFILE PICTURE MODAL --}}
    <div x-show="changePicModal" class="fixed inset-0 z-50 flex p-4 bg-black/50 overflow-y-auto" x-cloak>
        <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-xl relative m-auto flex-shrink-0 max-h-[90vh] overflow-y-auto">
            <button @click="changePicModal = false" class="absolute top-4 right-4 text-gray-500 hover:text-black">&times;</button>
            <h3 class="text-2xl font-black mb-4">Change Parent Picture</h3>
            <form action="{{ route('parent.updatePicture') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block font-bold mb-1">Select Parent Account</label>
                    <select name="parent_id" required class="w-full border rounded-lg p-2 font-bold bg-white">
                        <option value="" disabled selected>-- Select Parent --</option>
                        @foreach($parents ?? [] as $parent)
                           <option value="{{ $parent->user_id }}">{{ $parent->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block font-bold mb-1">Upload New Picture</label>
                    <input type="file" name="profile_picture" accept="image/*" required class="w-full border rounded-lg p-2 font-bold bg-gray-50">
                    <p class="text-xs text-gray-500 mt-1">Accepted formats: JPG, PNG. Max size: 2MB.</p>
                </div>
                <div class="flex justify-end gap-2 mt-4 shrink-0">
                    <button type="button" @click="changePicModal = false" class="px-4 py-2 bg-gray-200 rounded-lg font-bold">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold">Upload</button>
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