<!-- Advisory Class - teacher side -->
@extends('layouts.navigation')

@section('title', 'List of Students')

@section('content')
<div class="flex-1 p-8 bg-gray-100 min-h-screen" 
     x-data="{ 
         openModal: false, 
         messageModal: false, 
         promoteModal: false, 
         studentName: '', 
         studentId: '', 
         promoteUrl: '', 
         passwordModal: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }} 
     }">
     
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
                    <h3 class="text-3xl font-black text-amber-700 italic uppercase" style="-webkit-text-stroke: 1.5px black;">
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

        <div class="border-[3px] border-black rounded-lg overflow-x-auto bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] whitespace-nowrap">
            <table class="w-full text-left border-collapse min-w-max">
                
                <thead class="bg-gray-200 border-b-[3px] border-black text-lg font-bold">
                    <tr>
                        <th class="p-4 border-r-[3px] border-black text-center w-16">No.</th>
                        <th class="p-4 border-r-[3px] border-black w-40 text-center">LRN</th>
                        <th class="p-4 border-r-[3px] border-black">Learner</th>
                        <th class="p-4 border-r-[3px] border-black text-center w-48">Birthdate</th>
                        <th class="p-4 border-r-[3px] border-black text-center w-20">Message</th>
                        <th class="p-4 text-center w-32">Promotion</th>
                    </tr>
                </thead>
                
                <tbody>
                    
                    <tr class="bg-gray-300 font-bold border-b-[3px] border-black uppercase tracking-widest">
                        <td colspan="6" class="p-2 pl-4 italic border-r-[3px] border-black">Male</td>
                    </tr>
                    
                    @forelse($males as $index => $student)
                        <tr class="border-b-[3px] border-black hover:bg-yellow-50 transition cursor-pointer" onclick="window.location.href='{{ route('students.showStudent', $student->student_id ?? $student->id) }}'">
                            
                            <td class="p-4 border-r-[3px] border-black text-center font-bold">
                                {{ $index + 1 }}
                            </td>
                            
                            <td class="p-4 border-r-[3px] border-black font-bold text-center">
                                {{ $student->lrn }}
                            </td>
                            
                            <td class="p-4 border-r-[3px] border-black font-bold uppercase">
                                {{ $student->last_name }}, {{ $student->first_name }}
                            </td>
                            
                            <td class="p-4 border-r-[3px] border-black text-center font-medium">
                                {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : 'dd/mm/yyyy' }}
                            </td>
                            
                            <td class="p-2 border-r-[3px] border-black text-center">
                                <button type="button" 
                                        @click="event.stopPropagation(); studentName = '{{ $student->first_name }} {{ $student->last_name }}'; studentId = '{{ $student->student_id ?? $student->id }}'; messageModal = true;" 
                                        class="bg-blue-500 text-white w-9 h-9 rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-600 active:translate-y-1 active:shadow-none transition-all text-base">
                                    <i class="fa-solid fa-envelope"></i>
                                </button>
                            </td>
                            
                            <td class="p-2 text-center">
                                @if($student->promotion_status === 'pending')
                                    <div class="flex flex-col items-center justify-center cursor-default" onclick="event.stopPropagation();">
                                        <i class="fa-solid fa-clock-rotate-left text-amber-700 mb-1"></i>
                                        <span class="text-amber-700 font-bold italic text-[10px] uppercase tracking-wider">
                                            Pending: {{ is_numeric($student->next_grade_level) ? 'Grade ' . $student->next_grade_level : $student->next_grade_level }}
                                        </span>
                                    </div>
                                @else
                                    @php
                                        $gradeLvl = strtoupper(trim($student->grade_level));
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
                        <tr class="border-b-[3px] border-black">
                            <td colspan="6" class="p-4 text-center font-bold text-gray-500">
                                No male students found.
                            </td>
                        </tr>
                    @endforelse

                    <tr class="bg-gray-300 font-bold border-b-[3px] border-black uppercase tracking-widest">
                        <td colspan="6" class="p-2 pl-4 italic border-r-[3px] border-black">Female</td>
                    </tr>
                    
                    @forelse($females as $index => $student)
                        <tr class="border-b-[3px] border-black last:border-b-0 hover:bg-yellow-50 transition cursor-pointer" onclick="window.location.href='{{ route('students.showStudent', $student->student_id ?? $student->id) }}'">
                            
                            <td class="p-4 border-r-[3px] border-black text-center font-bold">
                                {{ $index + 1 }}
                            </td>
                            
                            <td class="p-4 border-r-[3px] border-black font-bold text-center">
                                {{ $student->lrn }}
                            </td>
                            
                            <td class="p-4 border-r-[3px] border-black font-bold uppercase">
                                {{ $student->last_name }}, {{ $student->first_name }}
                            </td>
                            
                            <td class="p-4 border-r-[3px] border-black text-center font-medium">
                                {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : 'dd/mm/yyyy' }}
                            </td>
                            
                            <td class="p-2 border-r-[3px] border-black text-center">
                                <button type="button" 
                                        @click="event.stopPropagation(); studentName = '{{ $student->first_name }} {{ $student->last_name }}'; studentId = '{{ $student->student_id ?? $student->id }}'; messageModal = true;" 
                                        class="bg-blue-500 text-white w-9 h-9 rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-600 active:translate-y-1 active:shadow-none transition-all text-base">
                                    <i class="fa-solid fa-envelope"></i>
                                </button>
                            </td>
                            
                            <td class="p-2 text-center">
                                @if($student->promotion_status === 'pending')
                                    <div class="flex flex-col items-center justify-center cursor-default" onclick="event.stopPropagation();">
                                        <i class="fa-solid fa-clock-rotate-left text-amber-700 mb-1"></i>
                                        <span class="text-amber-700 font-bold italic text-[10px] uppercase tracking-wider">
                                            Pending: {{ is_numeric($student->next_grade_level) ? 'Grade ' . $student->next_grade_level : $student->next_grade_level }}
                                        </span>
                                    </div>
                                @else
                                    @php
                                        $gradeLvl = strtoupper(trim($student->grade_level));
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
                        <tr>
                            <td colspan="6" class="p-4 text-center font-bold text-gray-500">
                                No female students found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
            
            <i class="fa-solid fa-circle-exclamation text-amber-700 text-6xl mb-4"></i>
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
</div>
@endsection