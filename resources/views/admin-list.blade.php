@extends('layouts.navigation')

@section('title', 'Admin List')

@section('content')
<div class="flex-1 p-4 md:p-8 bg-gray-100 min-h-screen" 
     x-data="{ 
         openModal: false, 
         archiveModal: false, 
         archiveUrl: '', 
         adminEditModal: false, 
         editAdminId: '', 
         editFirstName: '', 
         editMiddleName: '', 
         editLastName: ''
     }">

    <main class="max-w-6xl mx-auto">
        
        <!-- RESPONSIVE HEADER SECTION -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8 w-full">
            <div>
                <h2 class="text-3xl md:text-4xl font-black text-black uppercase tracking-tight leading-none">List of Accounts</h2>
                <h3 class="text-2xl md:text-4xl font-bold text-amber-700 mt-1 italic">Admins</h3>
            </div>
            
            <div class="flex flex-wrap gap-3 w-full lg:w-auto">
                <a href="{{ route('admin.archived') }}" class="flex-1 md:flex-none justify-center bg-gray-200 hover:bg-gray-300 text-black px-4 md:px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 border-2 border-black whitespace-nowrap text-sm md:text-base">
                    <i class="fa-solid fa-box-archive"></i> View Archives
                </a>
                <a href="{{ route('account.management') }}" class="flex-1 md:flex-none justify-center bg-gray-800 hover:bg-black text-white px-4 md:px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 border-2 border-black whitespace-nowrap text-sm md:text-base">
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
                        <th class="p-4">Admin Name</th>
                        <th class="p-4 w-40 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-gray-300 font-bold border-b-2 border-black uppercase tracking-widest"><td colspan="3" class="p-2 pl-4 italic">Male</td></tr>
                    @foreach($males as $index => $admin)
                        <tr class="border-b-2 border-black hover:bg-gray-50 transition">
                            <td class="p-4 border-r-2 border-black text-center font-bold">{{ $index + 1 }}</td>
                            <td class="p-4 font-bold uppercase">{{ $admin->name }}</td>
                            <td class="p-4">
                                <div class="flex justify-center gap-2 items-center">
                                    <button type="button" 
                                            @click="adminEditModal = true; 
                                                    editAdminId = '{{ $admin->user_id ?? $admin->id }}'; 
                                                    editFirstName = '{{ addslashes($admin->admin->first_name ?? '') }}'; 
                                                    editMiddleName = '{{ addslashes($admin->admin->middle_name ?? '') }}'; 
                                                    editLastName = '{{ addslashes($admin->admin->last_name ?? '') }}';"
                                            class="bg-[#34C759] hover:bg-green-600 transition-colors text-white px-4 py-1.5 rounded-full font-bold text-sm">
                                        Edit
                                    </button>
                                    
                                    <button type="button" 
                                        @click="archiveModal = true; archiveUrl = '{{ route('account.admin.archive', $admin->user_id ?? $admin->id) }}'" 
                                        title="Archive" 
                                        class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1.5 rounded-full font-bold text-sm transition-colors">
                                        <i class="fa-solid fa-box-archive"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    <tr class="bg-gray-300 font-bold border-b-2 border-black uppercase tracking-widest"><td colspan="3" class="p-2 pl-4 italic">Female</td></tr>
                    @foreach($females as $index => $admin)
                        <tr class="border-b-2 border-black hover:bg-gray-50 transition">
                            <td class="p-4 border-r-2 border-black text-center font-bold">{{ $index + 1 }}</td>
                           <td class="p-4 font-bold uppercase">{{ $admin->name }}</td>
                            <td class="p-4">
                                <div class="flex justify-center gap-2 items-center">
                                    <button type="button" 
                                            @click="adminEditModal = true; 
                                                    editAdminId = '{{ $admin->user_id ?? $admin->id }}'; 
                                                    editFirstName = '{{ addslashes($admin->admin->first_name ?? '') }}'; 
                                                    editMiddleName = '{{ addslashes($admin->admin->middle_name ?? '') }}'; 
                                                    editLastName = '{{ addslashes($admin->admin->last_name ?? '') }}';" 
                                            class="bg-[#34C759] hover:bg-green-600 transition-colors text-white px-4 py-1.5 rounded-full font-bold text-sm">
                                        Edit
                                    </button>
                                    
                                    <button type="button" 
                                        @click="archiveModal = true; archiveUrl = '{{ route('account.admin.archive', $admin->user_id ?? $admin->id) }}'" 
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

    <!-- EDIT ADMIN MODAL -->
    <div x-show="adminEditModal" class="fixed inset-0 z-[9999] flex p-4 bg-black/60 backdrop-blur-sm overflow-y-auto" x-cloak>
        <div @click.away="adminEditModal = false" class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-lg w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] mt-24 mx-auto mb-10 md:m-auto max-h-[70vh] flex flex-col flex-shrink-0">
            <div class="flex justify-between items-start mb-6 shrink-0">
                <h2 class="text-3xl font-black uppercase text-black">Edit Admin</h2>
                <button @click="adminEditModal = false" class="text-gray-400 hover:text-red-600 text-3xl"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form :action="'/admin/accounts/admin/' + editAdminId + '/edit'" method="POST" class="flex flex-col overflow-hidden">
                @csrf
                @method('PUT')

                <div class="overflow-y-auto pr-2 pb-4">
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
                </div>

                <div class="flex flex-col md:flex-row justify-end space-y-3 md:space-y-0 md:space-x-4 shrink-0 mt-4 pt-4 border-t-2 border-gray-100">
                    <button type="button" @click="adminEditModal = false" class="font-bold text-gray-500 hover:text-black uppercase tracking-wider px-4 py-3 md:py-0 text-center">Cancel</button>
                    <button type="submit" class="bg-yellow-400 text-black font-black uppercase tracking-wider px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-yellow-500 active:translate-y-1 active:shadow-none transition-all flex justify-center items-center">
                        <i class="fa-solid fa-save mr-2"></i> Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ARCHIVE CONFIRMATION MODAL -->
    <div x-show="archiveModal" x-transition:opacity class="fixed inset-0 z-[9999] flex p-4 bg-black/60 backdrop-blur-sm overflow-y-auto" x-cloak>
        <div class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] mt-24 mx-auto mb-10 md:m-auto max-h-[70vh] flex-shrink-0" @click.away="archiveModal = false">
            <div class="text-center">
                <i class="fa-solid fa-box-archive text-6xl text-[#ffb72b] mb-6"></i>
                <h2 class="text-3xl font-black mb-4 uppercase">Archive Account?</h2>
                <p class="text-lg font-medium text-gray-600 mb-8 leading-tight">
                    Are you sure you want to archive this admin account? They will no longer have access to the system.
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