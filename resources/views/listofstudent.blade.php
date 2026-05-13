<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Advisory Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-gray-100" x-data="{ 
    openModal: false, 
    messageModal: false, 
    promoteModal: false, 
    studentName: '', 
    studentId: '', 
    promoteUrl: '', 
    passwordModal: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }} 
}">

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
            <x-top-icon-button>
                <i class="fa-solid fa-bell"></i>
            </x-top-icon-button>
            
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="hover:scale-110 transition-transform focus:outline-none flex items-center">
                    <i class="fa-solid fa-circle-user text-orange-300 text-4xl"></i>
                </button>
                
                <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-2xl py-1 z-50 border border-gray-200 overflow-hidden" style="display: none;" x-cloak>
                    
                    @if(auth()->user()->role === 'teacher')
                        <button @click="passwordModal = true; open = false" class="flex w-full items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors font-bold">
                            <i class="fa-solid fa-key mr-3 text-gray-400"></i>
                            Change Password
                        </button>
                        <hr class="border-gray-100">
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors font-bold">
                            <i class="fa-solid fa-right-from-bracket mr-3"></i>
                            Logout
                        </button>
                    </form>
                    
                    <hr class="border-gray-100">
                    
                    <button @click="open = false" class="flex w-full items-center px-4 py-3 text-sm text-gray-500 hover:bg-gray-50 transition-colors">
                        <i class="fa-solid fa-xmark mr-3"></i>
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="flex min-h-screen">
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0 shadow-2xl z-40">
            <ul class="space-y-1">
                <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-sidebar-link>

                @if(auth()->user()->role === 'parent')
                    <x-sidebar-link href="{{ route('student.view') }}" icon="fa-solid fa-user-graduate" :active="request()->routeIs('student.view')">
                        Student Information
                    </x-sidebar-link>
                @endif

                @if(auth()->user()->role === 'teacher')
                    <x-sidebar-link href="{{ route('students.index') }}" icon="fa-solid fa-chalkboard-user" :active="request()->routeIs('students.*')">
                        Advisory Class
                    </x-sidebar-link>
                @endif

                @php
                    $calendarRoute = match(auth()->user()->role) {
                        'admin' => route('admin.student.participation'),
                        'parent' => route('student.calendar'),
                        default => route('student.calendar.index'),
                    };
                @endphp
                <x-sidebar-link href="{{ $calendarRoute }}" icon="fa-solid fa-calendar-days" :active="request()->routeIs('admin.student.participation') || request()->routeIs('student.calendar*')">
                    Student Calendar
                </x-sidebar-link>

                <x-sidebar-link href="{{ auth()->user()->role === 'parent' ? route('parent.reportcard') : route('reportcard.index') }}" icon="fa-solid fa-star" :active="request()->routeIs('reportcard.*') || request()->routeIs('parent.reportcard')">
                    Report Card
                </x-sidebar-link>

                <x-sidebar-link href="{{ auth()->user()->role === 'parent' ? route('parent.attendance') : route('attendance.index') }}" icon="fa-solid fa-calendar-check" :active="request()->routeIs('attendance.*') || request()->routeIs('parent.attendance')">
                    Attendance
                </x-sidebar-link>

                @if(auth()->user()->role === 'admin')
                    <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="request()->routeIs('account.management')">
                        Account Management
                    </x-sidebar-link>
                @endif
            </ul>
        </nav>

        <main class="flex-1 p-8">
            @if(session('success'))
                <div x-data="{ show: true }" 
                     x-show="show" 
                     x-init="setTimeout(() => show = false, 4000)" 
                     x-transition 
                     class="max-w-5xl mx-auto mb-6 flex items-center justify-between bg-[#8cc63f] border-[3px] border-black p-4 rounded-2xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                    <div class="flex items-center">
                        <i class="fa-solid fa-circle-check text-2xl mr-3 text-black"></i>
                        <p class="font-black uppercase tracking-wider text-black">
                            {{ session('success') }}
                        </p>
                    </div>
                    <button @click="show = false" class="text-black hover:scale-125 transition-transform">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
            @endif

            <div class="max-w-5xl mx-auto">
                
                <div class="mb-8 flex items-center justify-between">
                    <div class="flex items-center">
                        @if(!$hideBackButton)
                            <a href="{{ route('students.index') }}" class="text-red-600 text-5xl hover:scale-110 transition mr-6">
                                <i class="fa-solid fa-circle-arrow-left"></i>
                            </a>
                        @endif
                        <div>
                            <h2 class="text-4xl font-black text-black uppercase">Advisory Class</h2>
                            <h3 class="text-3xl font-black text-orange-300 italic uppercase" style="-webkit-text-stroke: 1.5px black;">
                                {{ $grade }} - {{ $section->section_name ?? 'General' }}
                            </h3>
                        </div>
                    </div>
                    <div class="text-right border-r-4 border-[#b91c1c] pr-4">
                        <p class="text-gray-500 font-bold uppercase tracking-widest text-sm mb-1">Adviser</p>
                        <p class="text-2xl font-black text-black uppercase">
                            {{ optional($students->first())->adviser_name ?? 'Surname, First Name, MI' }}
                        </p>
                    </div>
                </div>

                <div class="border-2 border-black rounded-lg overflow-x-auto bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] whitespace-nowrap">
                    <table class="w-full text-left border-collapse min-w-max">
                        
                        <thead class="bg-gray-200 border-b-2 border-black text-lg font-bold">
                            <tr>
                                <th class="p-4 border-r-2 border-black text-center w-16">No.</th>
                                <th class="p-4 border-r-2 border-black w-40">LRN</th>
                                <th class="p-4 border-r-2 border-black">Learner</th>
                                <th class="p-4 border-r-2 border-black text-center w-48">Birthdate</th>
                                <th class="p-4 border-r-2 border-black text-center w-20">Message</th>
                                <th class="p-4 text-center w-32">Promotion</th>
                            </tr>
                        </thead>
                        
                        <tbody>
                            
                            <tr class="bg-gray-300 font-bold border-b-2 border-black uppercase tracking-widest">
                                <td colspan="6" class="p-2 pl-4 italic">Male</td>
                            </tr>
                            
                            @forelse($males as $index => $student)
                                <tr class="border-b-2 border-black hover:bg-gray-50 transition cursor-pointer" onclick="window.location.href='{{ route('students.showStudent', $student->student_id ?? $student->id) }}'">
                                    
                                    <td class="p-4 border-r-2 border-black text-center font-bold">
                                        {{ $index + 1 }}
                                    </td>
                                    
                                    <td class="p-4 border-r-2 border-black font-bold">
                                        {{ $student->lrn }}
                                    </td>
                                    
                                    <td class="p-4 border-r-2 border-black font-bold uppercase">
                                        {{ $student->last_name }}, {{ $student->first_name }}
                                    </td>
                                    
                                    <td class="p-4 border-r-2 border-black text-center font-medium">
                                        {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : 'dd/mm/yyyy' }}
                                    </td>
                                    
                                    <td class="p-2 border-r-2 border-black text-center">
                                        <button type="button" 
                                                @click="event.stopPropagation(); studentName = '{{ $student->first_name }} {{ $student->last_name }}'; studentId = '{{ $student->student_id ?? $student->id }}'; messageModal = true;" 
                                                class="bg-blue-500 text-white w-9 h-9 rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-600 active:translate-y-1 active:shadow-none transition-all text-base">
                                            <i class="fa-solid fa-envelope"></i>
                                        </button>
                                    </td>
                                    
                                    <td class="p-2 text-center">
                                        @if($student->promotion_status === 'pending')
                                            <div class="flex flex-col items-center justify-center cursor-default" onclick="event.stopPropagation();">
                                                <i class="fa-solid fa-clock-rotate-left text-orange-500 mb-1"></i>
                                                <span class="text-orange-600 font-bold italic text-[10px] uppercase tracking-wider">
                                                    Pending: {{ is_numeric($student->next_grade_level) ? 'Grade ' . $student->next_grade_level : $student->next_grade_level }}
                                                </span>
                                            </div>
                                        @else
                                            @php
                                                $gradeLvl = strtoupper(trim($student->grade_level));
                                                // FIXED: Variable is correctly defined here
                                                $nkpLevels = ['NURSERY', 'KINDERGARTEN', 'KINDER', 'PREPARATORY'];
                                                
                                                if (in_array($gradeLvl, $nkpLevels)) {
                                                    $isPassed = (bool)($student->has_nkp_eval ?? false);
                                                    $isComplete = $isPassed;
                                                } else {
                                                    $requiredSubjects = 9; 
                                                    $passingMark = 75; 
                                                    $gradesCount = $student->grades->count(); 
                                                    $failingGrade = $student->grades->first(fn($g) => $g->final_grade < $passingMark); 
                                                    $isComplete = $gradesCount >= $requiredSubjects; 
                                                    $isPassed = $isComplete && !$failingGrade;
                                                }
                                            @endphp
                                            
                                            @if($isPassed)
                                                <button type="button" 
                                                        @click="event.stopPropagation(); studentName = '{{ $student->first_name }} {{ $student->last_name }}'; promoteUrl = '{{ route('teacher.promote', $student->student_id ?? $student->id) }}'; promoteModal = true;" 
                                                        class="bg-[#8cc63f] text-black text-xs font-black uppercase tracking-wider px-3 py-2 rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:bg-green-500 active:translate-y-1 active:shadow-none transition-all">
                                                    Promote
                                                </button>
                                            @else
                                                <button type="button" 
                                                        onclick="event.stopPropagation();" 
                                                        class="bg-gray-400 text-gray-600 text-xs font-black uppercase tracking-wider px-3 py-2 rounded-lg border-2 border-gray-500 opacity-60 cursor-not-allowed shadow-none" 
                                                        title="{{ !$isComplete ? 'Records Incomplete' : 'Failed Grades' }}">
                                                    Promote
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-b-2 border-black">
                                    <td colspan="6" class="p-4 text-center font-bold text-gray-500">
                                        No male students found.
                                    </td>
                                </tr>
                            @endforelse

                            <tr class="bg-gray-300 font-bold border-b-2 border-black uppercase tracking-widest">
                                <td colspan="6" class="p-2 pl-4 italic">Female</td>
                            </tr>
                            
                            @forelse($females as $index => $student)
                                <tr class="border-b-2 border-black hover:bg-gray-50 transition cursor-pointer" onclick="window.location.href='{{ route('students.showStudent', $student->student_id ?? $student->id) }}'">
                                    
                                    <td class="p-4 border-r-2 border-black text-center font-bold">
                                        {{ $index + 1 }}
                                    </td>
                                    
                                    <td class="p-4 border-r-2 border-black font-bold">
                                        {{ $student->lrn }}
                                    </td>
                                    
                                    <td class="p-4 border-r-2 border-black font-bold uppercase">
                                        {{ $student->last_name }}, {{ $student->first_name }}
                                    </td>
                                    
                                    <td class="p-4 border-r-2 border-black text-center font-medium">
                                        {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : 'dd/mm/yyyy' }}
                                    </td>
                                    
                                    <td class="p-2 border-r-2 border-black text-center">
                                        <button type="button" 
                                                @click="event.stopPropagation(); studentName = '{{ $student->first_name }} {{ $student->last_name }}'; studentId = '{{ $student->student_id ?? $student->id }}'; messageModal = true;" 
                                                class="bg-blue-500 text-white w-9 h-9 rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-600 active:translate-y-1 active:shadow-none transition-all text-base">
                                            <i class="fa-solid fa-envelope"></i>
                                        </button>
                                    </td>
                                    
                                    <td class="p-2 text-center">
                                        @if($student->promotion_status === 'pending')
                                            <div class="flex flex-col items-center justify-center cursor-default" onclick="event.stopPropagation();">
                                                <i class="fa-solid fa-clock-rotate-left text-orange-500 mb-1"></i>
                                                <span class="text-orange-600 font-bold italic text-[10px] uppercase tracking-wider">
                                                    Pending: {{ is_numeric($student->next_grade_level) ? 'Grade ' . $student->next_grade_level : $student->next_grade_level }}
                                                </span>
                                            </div>
                                        @else
                                            @php
                                                $gradeLvl = strtoupper(trim($student->grade_level));
                                                // FIXED: Variable is now safely defined in the female loop too!
                                                $nkpLevels = ['NURSERY', 'KINDERGARTEN', 'KINDER', 'PREPARATORY'];
                                                
                                                if (in_array($gradeLvl, $nkpLevels)) {
                                                    $isPassed = (bool)($student->has_nkp_eval ?? false);
                                                    $isComplete = $isPassed;
                                                } else {
                                                    $requiredSubjects = 9; 
                                                    $passingMark = 75; 
                                                    $gradesCount = $student->grades->count(); 
                                                    $failingGrade = $student->grades->first(fn($g) => $g->final_grade < $passingMark); 
                                                    $isComplete = $gradesCount >= $requiredSubjects; 
                                                    $isPassed = $isComplete && !$failingGrade;
                                                }
                                            @endphp
                                            
                                            @if($isPassed)
                                                <button type="button" 
                                                        @click="event.stopPropagation(); studentName = '{{ $student->first_name }} {{ $student->last_name }}'; promoteUrl = '{{ route('teacher.promote', $student->student_id ?? $student->id) }}'; promoteModal = true;" 
                                                        class="bg-[#8cc63f] text-black text-xs font-black uppercase tracking-wider px-3 py-2 rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:bg-green-500 active:translate-y-1 active:shadow-none transition-all">
                                                    Promote
                                                </button>
                                            @else
                                                <button type="button" 
                                                        onclick="event.stopPropagation();" 
                                                        class="bg-gray-400 text-gray-600 text-xs font-black uppercase tracking-wider px-3 py-2 rounded-lg border-2 border-gray-500 opacity-60 cursor-not-allowed shadow-none" 
                                                        title="{{ !$isComplete ? 'Records Incomplete' : 'Failed Grades' }}">
                                                    Promote
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-b-2 border-black">
                                    <td colspan="6" class="p-4 text-center font-bold text-gray-500">
                                        No female students found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div x-show="messageModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
        <div @click.away="messageModal = false" class="bg-white border-[3px] border-black rounded-[30px] p-8 w-full max-w-lg shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]">
            
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-3xl font-black uppercase text-black">Message Parent</h2>
                    <p class="text-sm text-gray-500 font-bold uppercase tracking-wider mt-1">Regarding: <span class="text-blue-600" x-text="studentName"></span></p>
                </div>
                <button @click="messageModal = false" class="text-gray-400 hover:text-red-600 text-3xl">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form action="{{ route('students.message') }}" method="POST">
                @csrf
                <input type="hidden" name="student_id" :value="studentId">
                
                <div class="mb-6">
                    <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Subject</label>
                    <input type="text" name="subject" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-blue-400">
                </div>
                
                <div class="mb-8">
                    <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Message</label>
                    <textarea name="message" rows="5" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-blue-400 resize-none"></textarea>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <button type="button" @click="messageModal = false" class="font-bold text-gray-500 uppercase tracking-wider px-4">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-500 text-white font-black uppercase tracking-wider px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-600 active:translate-y-1 active:shadow-none transition-all">
                        <i class="fa-solid fa-paper-plane mr-2"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="promoteModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
        <div @click.away="promoteModal = false" class="bg-white border-[3px] border-black rounded-[30px] p-8 w-full max-w-md shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] text-center">
            
            <i class="fa-solid fa-circle-exclamation text-orange-500 text-6xl mb-4"></i>
            <h2 class="text-3xl font-black uppercase text-black mb-2">Confirm Promotion</h2>
            <p class="text-gray-600 font-bold mb-8">Are you sure you want to queue <span class="text-blue-600 uppercase" x-text="studentName"></span> for the next grade level?</p>
            
            <form :action="promoteUrl" method="POST" class="flex justify-center space-x-4">
                @csrf
                <button type="button" @click="promoteModal = false" class="font-bold text-gray-500 hover:text-black uppercase tracking-wider px-4">
                    Cancel
                </button>
                <button type="submit" class="bg-[#8cc63f] text-black font-black uppercase tracking-wider px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-green-500 active:translate-y-1 active:shadow-none transition-all">
                    Yes, Promote
                </button>
            </form>
        </div>
    </div>

    @if(auth()->user()->role === 'teacher')
    <div x-show="passwordModal" x-transition:opacity class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        <div @click.away="passwordModal = false" class="bg-white border-[4px] border-black rounded-[2.5rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative" x-data="{ currentPassword: '', newPassword: '', confirmPassword: '' }">
            
            <button @click="passwordModal = false" class="absolute top-6 right-8 text-4xl font-black text-gray-400 hover:text-black transition-colors">&times;</button>
            <h2 class="text-3xl font-black italic uppercase tracking-tight mb-8">Change Password</h2>
            
            <form action="{{ route('user.password.update') }}" method="POST" @submit.prevent="if(newPassword === confirmPassword && currentPassword !== '') $el.submit()">
                @csrf
                @method('PUT')
                
                <div class="space-y-5">
                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">Current Password</label>
                        <input type="password" name="current_password" x-model="currentPassword" required class="w-full border-[3px] rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-all @error('current_password') border-red-500 bg-red-50 focus:ring-red-400 @else border-black focus:ring-green-400 bg-white @enderror">
                        @error('current_password')
                            <p class="text-red-500 font-bold text-sm mt-2 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">New Password</label>
                        <input type="password" name="password" x-model="newPassword" required class="w-full border-[3px] border-black rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 transition-all bg-white">
                    </div>
                    
                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">Confirm New Password</label>
                        <input type="password" name="password_confirmation" x-model="confirmPassword" required class="w-full border-[3px] rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-all" :class="(confirmPassword !== '' && newPassword !== confirmPassword) ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-black focus:ring-green-400 bg-white'">
                        <p x-show="confirmPassword !== '' && newPassword !== confirmPassword" x-transition class="text-red-500 font-bold text-sm mt-2 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> Passwords do not match!</p>
                    </div>
                </div>
                
                <div class="flex justify-end items-center gap-8 mt-10">
                    <button type="button" @click="passwordModal = false" class="text-black font-black uppercase tracking-widest hover:text-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="currentPassword === '' || newPassword === '' || confirmPassword === '' || newPassword !== confirmPassword" :class="(currentPassword === '' || newPassword === '' || confirmPassword === '' || newPassword !== confirmPassword) ? 'opacity-50 cursor-not-allowed' : 'hover:brightness-95 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none'" class="bg-[#22C55E] text-white font-black py-3 px-8 rounded-2xl border-[3px] border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> UPDATE
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</body>
</html>