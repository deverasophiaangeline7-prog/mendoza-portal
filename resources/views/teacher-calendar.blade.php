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
    
    /* Softened Tom Select Theme */
    .ts-control { border: 1px solid #e5e7eb !important; border-radius: 0.5rem !important; padding: 0.75rem !important; font-weight: 500 !important; background-color: #f9fafb !important; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important; }
    .ts-dropdown { border: 1px solid #e5e7eb !important; border-radius: 0.5rem !important; margin-top: 4px !important; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important; }
    .ts-control .item { background: #ffedd5 !important; color: #9a3412 !important; border: none !important; font-weight: 500 !important; border-radius: 0.375rem !important; padding: 2px 8px !important; }
</style>

<!-- Outermost wrapper housing the Alpine.js state -->
<div class="flex-1 bg-gray-50 min-h-screen" x-data="{ 
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
                <h2 class="text-3xl font-bold tracking-tight text-gray-900">Student Participation</h2>
            </div>
        </div>

        <div id="calendar-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $event)
            <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm flex flex-col h-[400px] hover:shadow-md transition-shadow duration-200">
                <div class="mb-5 flex-shrink-0">
                    <h4 class="text-xl font-bold text-gray-800 leading-tight mb-3 capitalize">{{ strtolower($event->event_title) }}</h4>
                    
                    <div class="flex flex-col gap-2">
                        <p class="font-medium text-gray-500 text-sm flex items-center gap-2">
                            <i class="fa-regular fa-calendar text-gray-400 w-4"></i>
                            {{ \Carbon\Carbon::parse($event->start_date)->format('F d, Y') }}
                        </p>
                        
                        @if($event->time)
                            @php
                                $timeParts = preg_split('/(\r\n|\n| - )/', $event->time);
                                $startTime = '';
                                if (isset($timeParts[0]) && trim($timeParts[0])) {
                                    try {
                                        $startTime = \Carbon\Carbon::parse(trim($timeParts[0]))->format('h:i A');
                                    } catch (\Exception $e) {
                                        $startTime = trim($timeParts[0]);
                                    }
                                }
                                $endTime = '';
                                if (isset($timeParts[1]) && trim($timeParts[1])) {
                                    try {
                                        $endTime = \Carbon\Carbon::parse(trim($timeParts[1]))->format('h:i A');
                                    } catch (\Exception $e) {
                                        $endTime = trim($timeParts[1]);
                                    }
                                }
                                $displayTime = implode(' - ', array_filter([$startTime, $endTime]));
                            @endphp
                            @if($displayTime)
                            <p class="font-medium text-gray-500 text-sm flex items-center gap-2">
                                <i class="fa-regular fa-clock text-gray-400 w-4"></i>
                                {{ $displayTime }}
                            </p>
                            @endif
                        @endif
                    </div>
                </div>
                
                <!-- Fixed size scrollable area for participants -->
                <div class="flex-1 border-t border-dashed border-gray-200 pt-5 flex flex-col overflow-hidden mt-1">
                    <h5 class="font-semibold text-[11px] uppercase mb-4 text-gray-400 tracking-wider flex-shrink-0">Event Program</h5>
                    
                    @php
                        // Group participants by student
                        $groupedByStudent = $event->participants->groupBy(function($participant) {
                            return $participant->student_id;
                        })->sortKeys();
                    @endphp

                    <div class="overflow-y-auto flex-1 pr-2 space-y-4 custom-scrollbar">
                        @forelse($groupedByStudent as $studentId => $studentParticipants)
                            @php 
                                $studentInfo = $studentParticipants->first()->student; 
                                $sectionString = $studentInfo->section ? ($studentInfo->section->grade_level . ' - ' . $studentInfo->section->section_name) : ''; 
                            @endphp
                            <div class="pb-3 border-b border-gray-50 last:border-b-0 last:pb-0">
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm font-semibold text-gray-800 capitalize">
                                            {{ strtolower($studentInfo->last_name ?? '') }}, {{ strtolower($studentInfo->first_name ?? '') }}
                                        </span>
                                        @if($sectionString)
                                            <span class="text-[10px] bg-gray-100 text-gray-500 font-medium px-1.5 py-0.5 rounded-md border border-gray-200">
                                                {{ $sectionString }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex flex-wrap gap-1.5 mt-0.5">
                                        @foreach($studentParticipants as $participant)
                                            <span class="inline-flex items-center text-[11px] bg-orange-50 text-orange-700 px-2 py-1 rounded-md font-medium border border-orange-100">
                                                {{ $participant->role ?: 'General Participant' }}
                                                
                                                @if(!in_array(strtolower(auth()->user()->role), ['admin', 'superadmin']) && in_array($participant->student_id, $myStudentIds))
                                                <button type="button" 
                                                    @click="deleteModal = true; deleteUrl = '{{ route('calendar.deleteParticipant', $participant->id) }}'; deleteParticipantName = '{{ addslashes(($studentInfo->last_name ?? '') . ', ' . ($studentInfo->first_name ?? '') . ' (' . ($participant->role ?: 'General Participant') . ')') }}'"
                                                    class="ml-2 text-orange-400 hover:text-orange-600 transition-colors" title="Remove role">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-center justify-center h-full">
                                <p class="text-sm text-gray-400 italic text-center">No participants assigned yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                @if(!in_array(strtolower(auth()->user()->role), ['admin', 'superadmin']))
                <div class="mt-4 pt-4 border-t border-gray-100 flex-shrink-0">
                    <button type="button" @click="
                        selectedEventId = '{{ $event->calendar_id }}'; 
                        selectedEventTitle = '{{ addslashes($event->event_title) }}'; 
                        openModal = true;
                    " class="w-full bg-white text-blue-600 border border-blue-200 font-semibold py-2.5 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors text-sm shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus text-xs"></i> Add Student
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
         class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-[100] p-4" 
         x-cloak x-transition
         x-init="$watch('openModal', value => { if(value) { initTomSelect(); } })">
        
        <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md relative m-auto flex-shrink-0" @click.away="openModal = false">
            <h3 class="text-xl font-bold mb-1 text-gray-900">Assign Participants</h3>
            <p class="text-gray-500 font-medium text-sm mb-6 capitalize" x-text="selectedEventTitle.toLowerCase()"></p>

            <form action="{{ route('calendar.addParticipant') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="event_id" :value="selectedEventId">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block font-semibold text-sm text-gray-700">Search Students</label>
                        <button type="button" onclick="selectAllStudents()" class="text-xs font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-md transition-colors">
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
                    <label class="block font-semibold text-sm text-gray-700 mb-2">Assign Role(s)</label>
                    <select id="role-select" name="roles[]" multiple placeholder="Select roles (optional)..." autocomplete="off">
                        <option value="Co-Emcee">Co-Emcee</option>
                        <option value="Contestant">Contestant</option>
                        <option value="Introducer">Introducer</option>
                        <option value="Performer">Performer</option>
                        <option value="Prayer Leader">Prayer Leader</option>
                    </select>
                </div>

                <div class="flex space-x-3 pt-6">
                    <button type="button" @click="openModal = false" class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 font-semibold rounded-lg text-sm hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg shadow-sm text-sm hover:bg-blue-700 transition-colors">Confirm Setup</button>
                </div>
            </form>
        </div>
    </div>

    <!-- REMOVE PARTICIPANT MODAL -->
    <div x-show="deleteModal" class="fixed inset-0 z-[100] flex p-4 bg-gray-900/40 backdrop-blur-sm overflow-y-auto" x-cloak x-transition>
        <div class="bg-white rounded-2xl p-8 max-w-sm w-full shadow-xl m-auto flex-shrink-0" @click.away="deleteModal = false">
            <div class="text-center">
                <div class="bg-red-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5 text-red-500">
                    <i class="fa-solid fa-user-xmark text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold mb-2 text-gray-900">Remove Role?</h2>
                <p class="text-sm text-gray-500 mb-6">
                    Are you sure you want to remove the role from <br><span class="font-semibold text-gray-900" x-text="deleteParticipantName"></span>?
                </p>
                <div class="flex flex-col gap-3">
                    <form :action="deleteUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white font-semibold py-2.5 rounded-lg shadow-sm hover:bg-red-700 transition-colors text-sm">Yes, remove role</button>
                    </form>
                    <button @click="deleteModal = false" type="button" class="w-full bg-white border border-gray-200 text-gray-700 font-semibold py-2.5 rounded-lg hover:bg-gray-50 transition-colors text-sm">Cancel</button>
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
                            return '<div class="create">Add <strong>' + escape(data.input) + '</strong> as a new role...</div>';
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

        setInterval(() => {
            if (typeof openModal === 'undefined' || (!openModal && !deleteModal)) {
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
            }
        }, 3000);
    </script>

    <!-- TOAST NOTIFICATIONS -->
    <div x-show="showSuccessToast" x-cloak x-transition class="fixed bottom-6 right-6 z-[200] px-6 py-3 rounded-lg shadow-lg bg-green-50 text-green-800 font-medium border border-green-200 text-sm flex items-center">
        <i class="fa-solid fa-circle-check mr-2 text-green-500"></i> {{ session('success') }}
    </div>

    <div x-show="showErrorToast" x-cloak x-transition class="fixed bottom-6 right-6 z-[200] px-6 py-3 rounded-lg shadow-lg bg-red-50 text-red-800 font-medium border border-red-200 text-sm flex items-center">
        <i class="fa-solid fa-circle-exclamation mr-2 text-red-500"></i> 
        @if($errors->any()) {{ $errors->first() }} @else Something went wrong! @endif
    </div>

</div>
@endsection