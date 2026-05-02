<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
        .form-input-pill {
            border: 2px solid black;
            border-radius: 0.75rem;
            height: 2.5rem;
            padding: 0 0.75rem;
            width: 100%;
        }
    </style>
</head>

<!-- Alpine variables updated here to include Edit Student state! -->
<body class="bg-gray-100" x-data="{ openModal: false, archiveModal: false, archiveUrl: '', studentEditModal: false, editStudentId: '', editFirstName: '', editMiddleName: '', editLastName: '', editSectionId: '' }">

    <header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-50">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow" alt="Logo">
            <h1 class="text-2xl font-bold uppercase tracking-tight">Mendoza Academy, Inc.</h1>
        </div>
        
        <div class="flex items-center space-x-6 text-2xl">
            <x-top-icon-button>
                <i class="fa-solid fa-envelope relative">
                    <span class="absolute -top-2 -right-2 bg-yellow-400 text-red-700 text-xs rounded-full h-5 w-5 flex items-center justify-center border border-red-700 font-bold">1</span>
                </i>
            </x-top-icon-button>
            <x-top-icon-button><i class="fa-solid fa-bell"></i></x-top-icon-button>
            
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="hover:scale-110 transition-transform focus:outline-none flex items-center">
                    <i class="fa-solid fa-circle-user text-orange-400 text-4xl"></i>
                </button>
                <div x-show="open" x-transition class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-2xl py-1 z-50 border border-gray-200 overflow-hidden" style="display: none;" x-cloak>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors font-bold"><i class="fa-solid fa-right-from-bracket mr-3"></i>Logout</button>
                    </form>
                    <hr class="border-gray-100">
                    <button @click="open = false" class="flex w-full items-center px-4 py-3 text-sm text-gray-500 hover:bg-gray-50 transition-colors"><i class="fa-solid fa-xmark mr-3"></i>Cancel</button>
                </div>
            </div>
        </div>
    </header>

    <div class="flex min-h-screen">
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0 shadow-2xl z-40">
            <ul class="space-y-1">
                <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line" :active="request()->routeIs('dashboard')">Dashboard</x-sidebar-link>
                @if(auth()->user()->role === 'parent')
                    <x-sidebar-link href="{{ route('student.view') }}" icon="fa-solid fa-user-graduate" :active="request()->routeIs('student.view')">Student Information</x-sidebar-link>
                @endif
                @if(auth()->user()->role === 'teacher')
                    <x-sidebar-link href="{{ route('students.index') }}" icon="fa-solid fa-chalkboard-user" :active="request()->routeIs('students.*')">Advisory Class</x-sidebar-link>
                @endif
                @php
                    $calendarRoute = match(auth()->user()->role) {
                        'admin' => route('admin.student.participation'),
                        'parent' => route('student.calendar'),
                        default => route('student.calendar.index'),
                    };
                @endphp
                <x-sidebar-link href="{{ $calendarRoute }}" icon="fa-solid fa-calendar-days" :active="request()->routeIs('admin.student.participation') || request()->routeIs('student.calendar*')">Student Calendar</x-sidebar-link>
                <x-sidebar-link href="{{ auth()->user()->role === 'parent' ? route('parent.reportcard') : route('reportcard.index') }}" icon="fa-solid fa-star" :active="request()->routeIs('reportcard.*') || request()->routeIs('parent.reportcard')">Report Card</x-sidebar-link>
                <x-sidebar-link href="{{ auth()->user()->role === 'parent' ? route('parent.attendance') : route('attendance.index') }}" icon="fa-solid fa-calendar-check" :active="request()->routeIs('attendance.*') || request()->routeIs('parent.attendance')">Attendance</x-sidebar-link>
                @if(auth()->user()->role === 'admin')
                    <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="request()->routeIs('account.management')">Account Management</x-sidebar-link>
                @endif
            </ul>
        </nav>

        <main class="flex-1 p-8">
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h2 class="text-4xl font-black text-black uppercase">Account Management</h2>
                    <h3 class="text-3xl font-black text-orange-400 italic uppercase" style="-webkit-text-stroke: 1.5px black;">
                        {{ str_replace('-', ' ', $grade) }} - {{ $section->section_name ?? 'General' }}
                    </h3>
                </div>
                
                <div class="flex gap-4">
                    <a href="{{ route('parent.archived') }}" class="bg-gray-200 hover:bg-gray-300 text-black px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 border-2 border-black">
                        <i class="fa-solid fa-box-archive"></i> View Archives
                    </a>
                    <a href="{{ route('parent.list') }}" class="bg-gray-800 hover:bg-black text-white px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 border-2 border-black">
                        <i class="fa-solid fa-arrow-left"></i> Back to Grades
                    </a>
                </div>
            </div>

            <div class="border-2 border-black rounded-lg overflow-hidden bg-white">
                <table class="w-full text-left border-collapse">
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
                                        <!-- WIRED UP EDIT BUTTON -->
                                        <button type="button" 
                                                @click="studentEditModal = true; 
                                                    editStudentId = '{{ $student->student_id }}'; 
                                                    editFirstName = '{{ addslashes($student->first_name) }}'; 
                                                    editMiddleName = '{{ addslashes($student->middle_name) }}'; 
                                                    editLastName = '{{ addslashes($student->last_name) }}';
                                                    editSectionId = '{{ $student->section_id }}';"
                                                class="bg-[#34C759] hover:bg-green-600 transition-colors text-white px-4 py-1.5 rounded-full font-bold text-sm">
                                            Edit
                                        </button>
                                        
                                        <!-- ARCHIVE BUTTON FOR MALES -->
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
                                        <!-- WIRED UP EDIT BUTTON -->
                                        <button type="button" 
                                                @click="studentEditModal = true; 
                                                        editStudentId = '{{ $student->student_id ?? $student->id }}'; 
                                                        editLrn = '{{ $student->lrn }}'; 
                                                        editFirstName = '{{ addslashes($student->first_name) }}'; 
                                                        editLastName = '{{ addslashes($student->last_name) }}';" 
                                                class="bg-[#34C759] hover:bg-green-600 transition-colors text-white px-4 py-1.5 rounded-full font-bold text-sm">
                                            Edit
                                        </button>
                                        
                                        <!-- ARCHIVE BUTTON FOR FEMALES -->
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
    </div>

    <!-- STUDENT EDIT MODAL -->
    <div x-show="studentEditModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        <div @click.away="studentEditModal = false" class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-lg w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]">
            <div class="flex justify-between items-start mb-6">
                <h2 class="text-3xl font-black uppercase text-black">Edit Student</h2>
                <button @click="studentEditModal = false" class="text-gray-400 hover:text-red-600 text-3xl"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form :action="'/admin/students/' + editStudentId + '/edit'" method="POST">
                @csrf
                @method('PUT')
                

                <div class="grid grid-cols-3 gap-4 mb-4">
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

                <div class="flex justify-end space-x-4">
                    <button type="button" @click="studentEditModal = false" class="font-bold text-gray-500 hover:text-black uppercase tracking-wider px-4">Cancel</button>
                    <button type="submit" class="bg-yellow-400 text-black font-black uppercase tracking-wider px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-yellow-500 active:translate-y-1 active:shadow-none transition-all">
                        <i class="fa-solid fa-save mr-2"></i> Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Archive Confirmation Modal (Unchanged) -->
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
</body>
</html>