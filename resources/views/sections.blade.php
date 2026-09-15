@extends('layouts.navigation')

@section('title', 'Account Management')

@section('content')
<div class="flex-1 p-4 md:p-8 bg-gray-100 min-h-screen" 
     x-data="{ 
         openModal: false, 
         archiveModal: false, 
         archiveUrl: '', 
         studentEditModal: false, 
         editStudentId: '', 
         editFirstName: '', 
         editMiddleName: '', 
         editLastName: '', 
         editSectionId: '',
         editLrn: '' 
     }">

    <main class="max-w-6xl mx-auto">
        
        <!-- RESPONSIVE HEADER SECTION -->
        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full">
            <div>
                <h2 class="text-3xl md:text-4xl font-black text-black uppercase leading-none">Account Management</h2>
                <h3 class="text-2xl md:text-3xl font-black text-amber-700 italic uppercase mt-1" style="-webkit-text-stroke: 1.5px black;">
                    {{ str_replace('-', ' ', $grade) }} - {{ $section->section_name ?? 'General' }}
                </h3>
            </div>
            
            <div class="flex flex-wrap gap-3 w-full md:w-auto">
                <a href="{{ route('parent.archived') }}" class="flex-1 md:flex-none justify-center bg-gray-200 hover:bg-gray-300 text-black px-4 md:px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 border-2 border-black whitespace-nowrap text-sm md:text-base">
                    <i class="fa-solid fa-box-archive"></i> View Archives
                </a>
                <a href="{{ route('parent.list') }}" class="flex-1 md:flex-none justify-center bg-gray-800 hover:bg-black text-white px-4 md:px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 border-2 border-black whitespace-nowrap text-sm md:text-base">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <!-- SCROLLABLE TABLE CONTAINER -->
        <div class="overflow-x-auto border-2 border-black rounded-lg shadow-sm bg-white w-full">
            <table class="w-full min-w-[600px] text-left border-collapse">
                <thead class="bg-gray-200 border-b-2 border-black text-xl font-bold">
                    <tr>
                        <th class="p-4 border-r-2 border-black text-center w-24">No.</th>
                        <th class="p-4 border-r-2 border-black w-40">LRN</th>
                        <th class="p-4">Learner</th>
                        <th class="p-4 w-40 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-gray-300 font-bold border-b-2 border-black uppercase tracking-widest"><td colspan="4" class="p-2 pl-4 italic">Male</td></tr>
                    @foreach($males as $index => $student)
                        <tr class="border-b-2 border-black hover:bg-gray-50 transition">
                            <td class="p-4 border-r-2 border-black text-center font-bold">{{ $index + 1 }}</td>
                            <td class="p-4 border-r-2 border-black font-bold">{{ $student->lrn }}</td>
                            <td class="p-4 font-bold uppercase">{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td class="p-4">
                                <div class="flex justify-center gap-2 items-center">
                                    <button type="button" 
                                            @click="studentEditModal = true; 
                                                    editStudentId = '{{ $student->student_id }}'; 
                                                    editLrn = '{{ $student->lrn }}';
                                                    editFirstName = '{{ addslashes($student->first_name) }}'; 
                                                    editMiddleName = '{{ addslashes($student->middle_name) }}'; 
                                                    editLastName = '{{ addslashes($student->last_name) }}';
                                                    editSectionId = '{{ $student->section_id }}';"
                                            class="bg-[#34C759] hover:bg-green-600 transition-colors text-white px-4 py-1.5 rounded-full font-bold text-sm">
                                        Edit
                                    </button>
                                    
                                    <button type="button" 
                                        @click="archiveModal = true; archiveUrl = '{{ route('account.parent.archive', $student->user_id) }}'" 
                                        title="Archive" 
                                        class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1.5 rounded-full font-bold text-sm transition-colors">
                                        <i class="fa-solid fa-box-archive"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    <tr class="bg-gray-300 font-bold border-b-2 border-black uppercase tracking-widest"><td colspan="4" class="p-2 pl-4 italic">Female</td></tr>
                    @foreach($females as $index => $student)
                        <tr class="border-b-2 border-black hover:bg-gray-50 transition">
                            <td class="p-4 border-r-2 border-black text-center font-bold">{{ $index + 1 }}</td>
                            <td class="p-4 border-r-2 border-black font-bold">{{ $student->lrn }}</td>
                            <td class="p-4 font-bold uppercase">{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td class="p-4">
                                <div class="flex justify-center gap-2 items-center">
                                    <button type="button" 
                                            @click="studentEditModal = true; 
                                                    editStudentId = '{{ $student->student_id ?? $student->id }}'; 
                                                    editLrn = '{{ $student->lrn }}'; 
                                                    editFirstName = '{{ addslashes($student->first_name) }}'; 
                                                    editLastName = '{{ addslashes($student->last_name) }}';" 
                                            class="bg-[#34C759] hover:bg-green-600 transition-colors text-white px-4 py-1.5 rounded-full font-bold text-sm">
                                        Edit
                                    </button>
                                    
                                    <button type="button" 
                                        @click="archiveModal = true; archiveUrl = '{{ route('account.parent.archive', $student->user_id) }}'" 
                                        title="Archive" 
                                        class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1.5 rounded-full font-bold text-sm transition-colors">
                                        <i class="fa-solid fa-box-archive"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </main>

    <div x-show="studentEditModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        <div @click.away="studentEditModal = false" class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-lg w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]">
            <div class="flex justify-between items-start mb-6">
                <h2 class="text-3xl font-black uppercase text-black">Edit Student</h2>
                <button @click="studentEditModal = false" class="text-gray-400 hover:text-red-600 text-3xl"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form :action="'/admin/students/' + editStudentId + '/edit'" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">First Name</label>
                        <input type="text" name="first_name" x-model="editFirstName" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-yellow-400 uppercase">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Middle Name</label>
                        <input type="text" name="middle_name" x-model="editMiddleName" class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-yellow-400 uppercase">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Last Name</label>
                        <input type="text" name="last_name" x-model="editLastName" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-yellow-400 uppercase">
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Assign to Section</label>
                    <select name="section_id" x-model="editSectionId" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-yellow-400 appearance-none bg-white">
                        <option value="">-- Select Section --</option>
                        @foreach(\App\Models\Section::orderBy('grade_level')->get() as $sec)
                            <option value="{{ $sec->section_id }}">{{ $sec->grade_level }} - {{ $sec->section_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col md:flex-row justify-end space-y-3 md:space-y-0 md:space-x-4">
                    <button type="button" @click="studentEditModal = false" class="font-bold text-gray-500 hover:text-black uppercase tracking-wider px-4 py-3 md:py-0 text-center">Cancel</button>
                    <button type="submit" class="bg-yellow-400 text-black font-black uppercase tracking-wider px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-yellow-500 active:translate-y-1 active:shadow-none transition-all flex justify-center items-center">
                        <i class="fa-solid fa-save mr-2"></i> Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="archiveModal" x-transition:opacity class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]" @click.away="archiveModal = false">
            <div class="text-center">
                <i class="fa-solid fa-box-archive text-6xl text-[#ffb72b] mb-6"></i>
                <h2 class="text-3xl font-black mb-4 uppercase">Archive Account?</h2>
                <p class="text-lg font-medium text-gray-600 mb-8 leading-tight">
                    Are you sure you want to archive this parent account? The student will be hidden from the active list.
                </p>
                <div class="flex flex-col gap-4">
                    <form :action="archiveUrl" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full bg-[#ffb72b] text-black font-black py-4 rounded-full border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-yellow-500 active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all">
                            YES, ARCHIVE
                        </button>
                    </form>
                    <button @click="archiveModal = false" type="button" class="w-full bg-gray-100 text-gray-700 font-black py-4 rounded-full border-2 border-black hover:bg-gray-200 transition-all">
                        CANCEL
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection