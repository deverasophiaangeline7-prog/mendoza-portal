@extends('layouts.navigation')

@section('title', 'Student Calendar')

@section('content')

@php
    $myStudentIds = [];
    if (!in_array(strtolower(auth()->user()->role), ['admin', 'superadmin'])) {
        $myStudentIds = \App\Models\Student::active()
            ->whereHas('section', function ($q) {
                $q->where('teacher_id', auth()->user()->user_id);
            })->pluck('student_id')->toArray();
    }
@endphp

<!-- Tom Select Styles and Scripts CDN -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
    [x-cloak] { display: none !important; }
    
    /* Tom Select Theme Matching System Design */
    .ts-control { border: 3px solid black !important; border-radius: 0.75rem !important; padding: 10px !important; font-weight: 700 !important; background-color: #f9fafb !important; }
    .ts-dropdown { border: 3px solid black !important; border-radius: 0.75rem !important; margin-top: 5px !important; }
    .ts-control .item { background: #fb923c !important; color: black !important; border: 2px solid black !important; font-weight: 800 !important; border-radius: 5px !important; }
    
    /* Custom Scrollbar for the Event Program */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #999; }
</style>

<!-- Outermost wrapper housing the Alpine.js state -->
<div class="flex-1 bg-white min-h-screen" x-data="{ 
    openModal: false,
    selectedEventId: null, 
    selectedEventTitle: '',
    deleteModal: false, 
    deleteUrl: '',
    deleteParticipantName: '',
    showErrorToast: {{ $errors->any() ? 'true' : 'false' }},
    showSuccessToast: {{ session('success') ? 'true' : 'false' }}
}" x-init="if(showErrorToast || showSuccessToast) setTimeout(() => { showErrorToast = false; showSuccessToast = false; }, 5000)">

    <main class="p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-4xl font-black uppercase tracking-tighter text-gray-900">Student Participation</h2>
            </div>
        </div>

        <div id="calendar-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($events as $event)
            <!-- Card Container -->
            <div class="bg-white border-[3px] border-black p-6 rounded-[2rem] shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] flex flex-col h-[420px]">
                
                <!-- Event Header -->
                <div class="mb-4 flex-shrink-0">
                    <h4 class="text-2xl font-black text-red-600 uppercase leading-tight mb-1">{{ $event->event_title }}</h4>
                    
                    <div class="flex flex-col gap-0">
                        <p class="font-bold text-gray-400 italic text-sm flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar w-4"></i>
                            {{ \Carbon\Carbon::parse($event->start_date)->format('F d, Y') }}
                        </p>
                        
                        @php
                            $displayTime = 'No time set';
                            if($event->time) {
                                $timeParts = preg_split('/(\r\n|\n| - )/', $event->time);
                                $startTime = '';
                                if (isset($timeParts[0]) && trim($timeParts[0])) {
                                    try { $startTime = \Carbon\Carbon::parse(trim($timeParts[0]))->format('h:i A'); } 
                                    catch (\Exception $e) { $startTime = trim($timeParts[0]); }
                                }
                                $endTime = '';
                                if (isset($timeParts[1]) && trim($timeParts[1])) {
                                    try { $endTime = \Carbon\Carbon::parse(trim($timeParts[1]))->format('h:i A'); } 
                                    catch (\Exception $e) { $endTime = trim($timeParts[1]); }
                                }
                                $parsedTime = implode(' - ', array_filter([$startTime, $endTime]));
                                if ($parsedTime) {
                                    $displayTime = $parsedTime;
                                }
                            }
                        @endphp
                        
                        <p class="font-bold text-gray-400 italic text-sm flex items-center gap-1.5">
                            <i class="fa-regular fa-clock w-4"></i>
                            {{ $displayTime }}
                        </p>
                    </div>
                </div>
                
                <!-- Scrollable area for participants -->
                <div class="flex-1 border-t-2 border-dashed border-black pt-4 flex flex-col overflow-hidden">
                    <h5 class="font-black text-[10px] uppercase mb-4 text-black tracking-widest text-center flex-shrink-0">Event Program</h5>
                    
                    @php
                        $groupedByStudent = $event->participants->groupBy(function($participant) {
                            return $participant->student_id;
                        })->sortKeys();
                    @endphp

                    <div class="overflow-y-auto flex-1 pr-2 custom-scrollbar">
                        @forelse($groupedByStudent as $studentId => $studentParticipants)
                            @php 
                                $studentInfo = $studentParticipants->first()->student; 
                                $sectionString = $studentInfo->section ? ($studentInfo->section->grade_level . ' - ' . $studentInfo->section->section_name) : ''; 
                            @endphp
                            
                            <!-- Individual Student Row -->
                            <div class="mb-4 last:mb-0">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <span class="text-sm font-black text-gray-900 capitalize">
                                            {{ strtolower($studentInfo->last_name ?? '') }}, {{ strtolower($studentInfo->first_name ?? '') }}
                                        </span>
                                        @if($sectionString)
                                            <span class="text-[9px] bg-gray-100 text-gray-600 font-extrabold px-2 py-0.5 rounded border border-gray-300 capitalize">
                                                {{ strtolower($sectionString) }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Role Tags Container -->
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($studentParticipants as $participant)
                                            <span class="inline-flex items-center gap-1 text-[11px] bg-[#b45309] text-white px-2 py-1 rounded font-bold capitalize">
                                                {{ strtolower($participant->role ?: 'General Participant') }}
                                                
                                                @if(!in_array(strtolower(auth()->user()->role), ['admin', 'superadmin']) && in_array($participant->student_id, $myStudentIds))
                                                <button type="button" 
                                                    @click="deleteModal = true; deleteUrl = '{{ route('calendar.deleteParticipant', $participant->id) }}'; deleteParticipantName = '{{ addslashes(ucwords(strtolower($studentInfo->last_name ?? '')) . ', ' . ucwords(strtolower($studentInfo->first_name ?? '')) . ' (' . ($participant->role ?: 'General Participant') . ')') }}'"
                                                    class="text-amber-200 hover:text-white transition-colors flex items-center justify-center" title="Remove role">
                                                    <i class="fa-solid fa-xmark text-[11px] ml-0.5"></i>
                                                </button>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-center justify-center h-full">
                                <p class="text-sm font-bold text-gray-300 italic text-center">No participants assigned yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Add Button anchored to bottom -->
                @if(!in_array(strtolower(auth()->user()->role), ['admin', 'superadmin']))
                <div class="mt-3 flex-shrink-0">
                    <button type="button" @click="
                        selectedEventId = '{{ $event->calendar_id }}'; 
                        selectedEventTitle = '{{ addslashes($event->event_title) }}'; 
                        openModal = true;
                    " class="w-full bg-blue-600 text-white font-black py-2.5 rounded-full shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-700 active:translate-y-[2px] active:translate-x-[2px] active:shadow-none transition-all uppercase text-sm tracking-wide">
                        + Add Student
                    </button>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </main>

    <!-- ASSIGN PARTICIPANTS MODAL -->
    @if(!in_array(strtolower(auth()->user()->role), ['admin', 'superadmin']))
    <div x-show="openModal" 
         class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[100] p-4" 
         x-cloak x-transition
         x-init="$watch('openModal', value => { if(value) { initTomSelect(); } })">
        
        <div class="bg-white p-8 rounded-[2rem] shadow-2xl w-full max-w-md border-[4px] border-black relative m-auto flex-shrink-0" @click.away="openModal = false">
            <h3 class="text-3xl font-black mb-1 text-red-600 uppercase italic">Assign Participants</h3>
            <p class="text-gray-500 font-bold uppercase text-[10px] mb-6" x-text="selectedEventTitle"></p>

            <form action="{{ route('calendar.addParticipant') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="event_id" :value="selectedEventId">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block font-black text-xs uppercase text-gray-400 tracking-wider">Search Students</label>
                        <button type="button" onclick="selectAllStudents()" class="text-[10px] font-black bg-gray-200 hover:bg-black hover:text-white text-black px-2 py-1 rounded-lg border-2 border-black uppercase transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[1px] active:translate-y-[1px]">
                            Select All
                        </button>
                    </div>
                    <select id="student-select" name="student_ids[]" multiple placeholder="Type name..." autocomplete="off">
                        @foreach($students as $student)
                            <option value="{{ $student->student_id }}">
                                {{ $student->first_name }} {{ $student->last_name }} @if($student->section)({{ $student->section->grade_level }} - {{ $student->section->section_name }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-black text-xs uppercase text-gray-400 tracking-wider mb-2">Assign Role(s)</label>
                    <select id="role-select" name="roles[]" multiple placeholder="Select roles (optional)..." autocomplete="off">
                        <option value="Co-Emcee">Co-Emcee</option>
                        <option value="Contestant">Contestant</option>
                        <option value="Introducer">Introducer</option>
                        <option value="Performer">Performer</option>
                        <option value="Prayer Leader">Prayer Leader</option>
                    </select>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" @click="openModal = false" class="flex-1 px-4 py-3 bg-gray-100 border-[3px] border-black font-black rounded-xl uppercase text-xs hover:bg-gray-200 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-green-400 text-black font-black border-[3px] border-black rounded-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] uppercase text-xs active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- REMOVE PARTICIPANT MODAL -->
    <div x-show="deleteModal" class="fixed inset-0 z-[100] flex p-4 bg-black/60 backdrop-blur-sm overflow-y-auto" x-cloak x-transition>
        <div class="bg-white border-[4px] border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] m-auto flex-shrink-0" @click.away="deleteModal = false">
            <div class="text-center">
                <div class="bg-red-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 border-[4px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <i class="fa-solid fa-user-xmark text-4xl text-red-600"></i>
                </div>
                <h2 class="text-3xl font-black mb-2 uppercase text-black">Remove Role?</h2>
                <p class="text-lg font-bold text-gray-600 mb-8 leading-tight">
                    Are you sure you want to remove <br><span class="font-black text-black underline" x-text="deleteParticipantName"></span>?
                </p>
                <div class="flex flex-col gap-3">
                    <form :action="deleteUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 text-white font-black py-4 rounded-xl border-[4px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-red-600 active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all uppercase text-xl tracking-wider">Yes, Remove</button>
                    </form>
                    <button @click="deleteModal = false" type="button" class="w-full bg-gray-100 text-gray-700 font-black py-4 rounded-xl border-[4px] border-black hover:bg-gray-200 transition-all uppercase text-lg tracking-wider">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        function initTomSelect() {
            let select = document.getElementById('student-select');
            if(!document.getElementById('student-select').tomselect) {
                new TomSelect("#student-select", { 
                    plugins: ['remove_button'], 
                    maxItems: 100, 
                    persist: false 
                });
            }
            
            if(!document.getElementById('role-select').tomselect) {
                new TomSelect("#role-select", { 
                    plugins: ['remove_button'], 
                    persist: false,
                    create: true, 
                    createOnBlur: true, 
                    render: {
                        option_create: function(data, escape) {
                            return '<div class="create font-bold">Add <strong>' + escape(data.input) + '</strong> as a new role...</div>';
                        },
                    }
                });
            }
        }

        function selectAllStudents() {
            let select = document.getElementById('student-select');
            if (select && select.tomselect) {
                let allValues = Array.from(select.options).map(opt => opt.value);
                select.tomselect.setValue(allValues);
            }
        }

        let isSubmitting = false;
        document.addEventListener('submit', () => { isSubmitting = true; });

        setInterval(() => {
            if (isSubmitting) return;

            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newCalendar = doc.getElementById('calendar-container');
                    const oldCalendar = document.getElementById('calendar-container');
                    
                    if (newCalendar && oldCalendar && newCalendar.innerHTML !== oldCalendar.innerHTML) {
                        oldCalendar.innerHTML = newCalendar.innerHTML;
                    }
                })
                .catch(err => console.error('Sync error:', err));
        }, 3000);
    </script>

    <!-- TOAST NOTIFICATIONS -->
    <div x-show="showSuccessToast" x-cloak x-transition
         class="fixed bottom-10 right-10 z-[999999] flex items-center justify-between gap-6 w-auto max-w-[calc(100vw-40px)] rounded-xl border-[3px] border-black px-6 py-4 font-black"
         style="background-color: #d1fae5; color: #065f46;">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-2xl shrink-0"></i>
            <span class="text-base whitespace-nowrap leading-none">{{ session('success') }}</span>
        </div>
        <button type="button" @click="showSuccessToast = false" class="text-2xl hover:scale-110 transition ml-2 shrink-0 flex items-center" style="background: none; border: none; cursor: pointer; color: inherit;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div x-show="showErrorToast" x-cloak x-transition
         class="fixed bottom-10 right-10 z-[999999] flex items-center justify-between gap-6 w-auto max-w-[calc(100vw-40px)] rounded-xl border-[3px] border-black px-6 py-4 font-black"
         style="background-color: #fee2e2; color: #7f1d1d;">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-2xl shrink-0"></i>
            <span class="text-base whitespace-nowrap leading-none">@if($errors->any()) {{ $errors->first() }} @else Something went wrong! @endif</span>
        </div>
        <button type="button" @click="showErrorToast = false" class="text-2xl hover:scale-110 transition ml-2 shrink-0 flex items-center" style="background: none; border: none; cursor: pointer; color: inherit;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

</div>
@endsection