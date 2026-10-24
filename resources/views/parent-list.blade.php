@extends('layouts.navigation')

@section('title', 'Parent Accounts')

@section('content')
<main class="flex-1 p-8 bg-white min-h-screen" x-data="{ addSectionModal: false, deleteSectionModal: false, changePicModal: false }">
    
    {{-- ADD THIS TOAST BLOCK HERE --}}
    @if(session()->has('toast_status'))
        <div id="import-toast" class="fixed bottom-10 right-10 z-[999999] flex items-center justify-between gap-4 min-w-[320px] rounded-xl border-[3px] border-black {{ session('toast_status') === 'success' ? 'bg-green-100 text-green-900' : 'bg-red-100 text-red-900' }} px-5 py-4 font-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] transition-opacity duration-500">
            <div class="flex items-center gap-3">
                @if(session('toast_status') === 'success')
                    <i class="fa-solid fa-circle-check text-2xl"></i>
                @else
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                @endif
                <span class="text-base">{{ session('toast_message') }}</span>
            </div>
            <button onclick="document.getElementById('import-toast').style.display='none'" class="text-2xl hover:scale-110 transition ml-2">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <script>
            setTimeout(function() {
                var toast = document.getElementById('import-toast');
                if (toast) {
                    toast.style.opacity = '0';
                    setTimeout(function() { toast.style.display = 'none'; }, 500);
                }
            }, 5000);
        </script>
    @endif
    <div class="max-w-6xl mx-auto">
        @if ($errors->any())
            <div class="bg-red-100 border-4 border-red-500 text-red-900 px-6 py-4 rounded-xl mb-6 font-bold shadow-[4px_4px_0px_0px_rgba(239,68,68,1)]">
                <p class="text-xl mb-2"><i class="fa-solid fa-triangle-exclamation"></i> Upload Failed. Here is why:</p>
                <ul class="list-disc ml-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <!-- RESPONSIVE HEADER SECTION -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8 w-full">
            <div>
                <h2 class="text-3xl md:text-4xl font-black text-black uppercase tracking-tight leading-none">List of Accounts</h2>
                <h3 class="text-2xl md:text-4xl font-bold text-amber-700 mt-1 italic">Parents</h3>
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
            <!-- Note: Ensure your route is correct and the form handles multipart data for file uploads -->
            <form action="{{ route('parent.updatePicture') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block font-bold mb-1">Select Parent Account</label>
                    <select name="parent_id" required class="w-full border rounded-lg p-2 font-bold bg-white">
                        <option value="" disabled selected>-- Select Parent --</option>
                        <!-- Example loop: You will need to pass $parents from your controller -->
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

    {{-- ADD SECTION MODAL (Fixed Mobile Centering) --}}
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

    {{-- DELETE SECTION MODAL (Fixed Mobile Centering) --}}
    <div x-show="deleteSectionModal" class="fixed inset-0 z-50 flex p-4 bg-black/50 overflow-y-auto" x-cloak>
        <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-xl relative m-auto flex-shrink-0 max-h-[90vh] overflow-y-auto">
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
                <div class="flex justify-end gap-2 mt-4 shrink-0">
                    <button type="button" @click="deleteSectionModal = false" class="px-4 py-2 bg-gray-200 rounded-lg font-bold">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg font-bold">Delete</button>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection