@extends('layouts.navigation')

@section('title', 'Student Calendar')

@section('content')
<!-- Tom Select Styles and Scripts CDN -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
    [x-cloak] { display: none !important; }
    
    /* Tom Select Theme Matching */
    .ts-control { border: 2px solid black !important; border-radius: 0.75rem !important; padding: 10px !important; font-weight: 700 !important; background-color: #f9fafb !important; }
    .ts-dropdown { border: 2px solid black !important; border-radius: 0.75rem !important; margin-top: 5px !important; }
    .ts-control .item { background: #fb923c !important; color: black !important; border: 1px solid black !important; font-weight: 800 !important; border-radius: 5px !important; }
</style>

<!-- Outermost wrapper housing the Alpine.js state from the original body element -->
<div class="flex-1 bg-gray-100 min-h-screen" x-data="{ 
    openModal: false, 
    selectedEventId: null, 
    selectedEventTitle: '',
    showErrorToast: {{ $errors->any() ? 'true' : 'false' }},
    showSuccessToast: {{ session('success') ? 'true' : 'false' }}
}" x-init="if(showErrorToast || showSuccessToast) setTimeout(() => { showErrorToast = false; showSuccessToast = false; }, 5000)">

    <main class="p-8 bg-white">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-4xl font-black uppercase tracking-tighter text-gray-900">Student Participation</h2>
                @if(auth()->user()->role === 'admin')
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($events as $event)
            <div class="bg-white border-[3px] border-black p-6 rounded-3xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] flex flex-col h-full">
                <div class="mb-4">
                    <h4 class="text-3xl font-black text-red-600 uppercase leading-tight">{{ $event->event_title }}</h4>
                    <p class="font-bold text-gray-400 italic text-sm">{{ \Carbon\Carbon::parse($event->start_date)->format('F d, Y') }}</p>
                </div>
                
                <div class="flex-1 border-t-2 border-dashed border-black pt-4">
                    <h5 class="font-black text-[10px] uppercase mb-4 text-gray-400 tracking-widest text-center">Event Program</h5>
                    
                    @forelse($event->participants->groupBy('role') as $role => $group)
                        <div class="mb-6 last:mb-0">
                            <div class="flex items-center mb-2">
                                <span class="text-[10px] bg-amber-700 text-white px-2 py-0.5 rounded font-black uppercase tracking-tighter">
                                    {{ $role ?: 'General Participant' }}
                                </span>
                                <div class="flex-1 border-b border-amber-200 ml-2"></div>
                            </div>

                            <ul class="space-y-1 ml-1">
                                @foreach($group as $participant)
                                    <li class="text-sm font-bold flex justify-between items-center group/item hover:bg-gray-50 rounded-lg px-2 py-1 transition-colors">
                                        <span class="text-gray-800">{{ $participant->student->last_name }}, {{ $participant->student->first_name }}</span>
                                        
                                        @if(auth()->user()->role !== 'admin')
                                        <form action="{{ route('calendar.deleteParticipant', $participant->id) }}" method="POST" onsubmit="return confirm('Remove student from this role?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-300 hover:text-red-600 transition-colors opacity-0 group-hover/item:opacity-100">
                                                <i class="fa-solid fa-xmark text-xs"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <p class="text-sm text-gray-300 italic text-center py-4">No participants assigned yet.</p>
                    @endforelse
                </div>

                @if(auth()->user()->role !== 'admin')
                <div class="mt-4 pt-4 border-t-2 border-black">
                    <button type="button" @click="
                        selectedEventId = '{{ $event->calendar_id }}'; 
                        selectedEventTitle = '{{ addslashes($event->event_title) }}'; 
                        openModal = true;
                    " class="w-full bg-blue-600 text-white font-black border-2 border-black py-2 rounded-xl shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-700 transition-all uppercase text-sm">
                        + Add Student
                    </button>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </main>

    <!-- ASSIGN PARTICIPANTS MODAL -->
    @if(auth()->user()->role !== 'admin')
    <div x-show="openModal" 
         class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[100]" 
         x-cloak x-transition
         x-init="$watch('openModal', value => { if(value) { initTomSelect(); } })">
        
        <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md border-4 border-black relative" @click.away="openModal = false">
            <h3 class="text-2xl font-black mb-1 text-red-880 uppercase italic">Assign Participants</h3>
            <p class="text-gray-500 font-bold uppercase text-[10px] mb-6" x-text="selectedEventTitle"></p>

            <form action="{{ route('calendar.addParticipant') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="event_id" :value="selectedEventId">
                <div>
                    <label class="block font-black text-xs uppercase text-gray-400 mb-2">Search Students</label>
                    <select id="student-select" name="student_ids[]" multiple placeholder="Type name..." autocomplete="off">
                        @foreach($students as $student)
                            <option value="{{ $student->student_id }}">{{ $student->last_name }}, {{ $student->first_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-black text-xs uppercase text-gray-400 mb-2">Assign Role(s)</label>
                    <select id="role-select" name="roles[]" multiple placeholder="Select roles (optional)..." autocomplete="off">
                        <option value="Co-Emcee">Co-Emcee</option>
                        <option value="Contestant">Contestant</option>
                        <option value="Introducer">Introducer</option>
                        <option value="Performer">Performer</option>
                        <option value="Prayer Leader">Prayer Leader</option>
                    </select>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" @click="openModal = false" class="flex-1 px-4 py-3 bg-gray-100 border-2 border-black font-black rounded-xl uppercase text-xs">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-green-400 text-black font-black border-2 border-black rounded-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] uppercase text-xs">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function initTomSelect() {
            if(!document.getElementById('student-select').tomselect) {
                new TomSelect("#student-select", { 
                    plugins: ['remove_button'], 
                    maxItems: 50, 
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
    </script>
    @endif

    <!-- TOAST NOTIFICATIONS -->
    <div x-show="showSuccessToast" x-cloak x-transition class="fixed bottom-10 right-10 z-[200] px-8 py-4 rounded-2xl border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-green-400 text-black font-black uppercase">
        <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
    </div>

    <div x-show="showErrorToast" x-cloak x-transition class="fixed bottom-10 right-10 z-[200] px-8 py-4 rounded-2xl border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-red-500 text-white font-black uppercase">
        <i class="fa-solid fa-circle-exclamation mr-2"></i> 
        @if($errors->any()) {{ $errors->first() }} @else Something went wrong! @endif
    </div>

</div>
@endsection