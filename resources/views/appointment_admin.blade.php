@extends('layouts.navigation')

@section('title', 'Appointment Scheduling (Admin)')

@section('content')
<style>
    :root {
        --ma-red: #d00101;
        --ma-orange: #e68a2d; 
        --ma-green: #34a853;
        --ma-bg-grey: #e8e8e8;
        --ma-dark-grey: #b0b0b0; 
    }

    .dashboard-container { display: flex; font-family: 'Arial', sans-serif; width: 100%; height: 100%; overflow: hidden; }
    .main-content { flex: 1; padding: 30px 50px; background-color: #ffffff; display: flex; flex-direction: column; align-items: center; overflow-y: auto; }
    .page-title { font-size: 36px; font-weight: 900; text-align: center; margin-bottom: 40px; text-transform: uppercase; }
    
    .adviser-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; width: 100%; max-width: 1100px; }
    .adviser-btn { background-color: var(--ma-orange); border: 3px solid #000; border-radius: 40px; padding: 25px 15px; text-align: center; cursor: pointer; transition: transform 0.2s; display: block; }
    .adviser-btn:hover { transform: translateY(-5px); }
    .adviser-btn span { display: block; color: #fff; text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000; font-weight: 900; font-size: 22px; }

    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); display: flex; justify-content: center; align-items: center; z-index: 1000; }
    .hidden { display: none !important; }
    .admin-modal { background: white; border: 4px solid #000; border-radius: 25px; width: 95%; max-width: 1400px; padding: 20px 30px; position: relative; }
    
    .modal-header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .calendar-navigation { display: flex; align-items: center; gap: 15px; }
    .nav-arrow { color: var(--ma-orange); font-size: 32px; text-decoration: none; font-weight: 900; cursor: pointer; }
    .month-title { font-size: 24px; font-weight: 900; margin: 0; min-width: 300px; text-align: center; }
    
    .manage-btn { color: var(--ma-green); font-size: 20px; font-weight: 900; background: none; border: none; cursor: pointer; }
    .close-btn { background: var(--ma-red); color: white; border: 3px solid #fff; border-radius: 50%; width: 45px; height: 45px; font-size: 28px; cursor: pointer; }

    .schedule-grid { width: 100%; border-collapse: collapse; text-align: center; border: 2px solid #000; }
    .schedule-grid th, .schedule-grid td { border: 2px solid #000; padding: 5px; height: 40px; font-size: 13px; font-weight: 900; }
    .schedule-grid th { background-color: var(--ma-bg-grey); }
    .time-col { background-color: var(--ma-bg-grey); width: 80px; }
    
    .cell-red { background-color: var(--ma-red); }
    .cell-green { background-color: var(--ma-green); }
    .cell-white { background-color: #ffffff; }
    .cell-grey { background-color: var(--ma-dark-grey); }

    .legend { display: flex; justify-content: center; gap: 15px; margin-top: 15px; font-weight: 900; }
    .legend-item span { display: inline-block; width: 18px; height: 18px; border-radius: 50%; border: 2px solid #000; vertical-align: middle; margin-right: 5px; }
</style>

<div class="dashboard-container">
    <div class="main-content">
        <h1 class="page-title">Appointment Scheduling</h1>
        <div class="adviser-grid">
            @foreach($advisers as $adviser)
                @php $assigned = !empty($adviser['user_id']); @endphp
                <div class="adviser-btn {{ $assigned ? 'cursor-pointer' : 'opacity-50 cursor-not-allowed' }}" @if($assigned) onclick="openAdminModal('{{ $adviser['section'] }}', '{{ addslashes($adviser['name']) }}', '{{ $adviser['user_id'] }}')" @endif>
                    <span>{{ $adviser['section'] }}</span>
                    <span>{{ $assigned ? $adviser['name'] : 'Unassigned' }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div id="adminCalendarModal" class="modal-overlay hidden">
    <div class="admin-modal">
        @php
            $dateParam = request('date', \Carbon\Carbon::now()->format('Y-m-d'));
            $currentDate = \Carbon\Carbon::parse($dateParam);
            $prevWeekDate = $currentDate->copy()->subWeek()->format('Y-m-d');
            $nextWeekDate = $currentDate->copy()->addWeek()->format('Y-m-d');
            $startOfWeek = $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            $calendarDays = [];
            for ($i = 0; $i < 5; $i++) { $calendarDays[] = $startOfWeek->copy()->addDays($i); }
            $timeSlots = ['8AM', '9AM', '10AM', '11AM', '1PM', '2PM', '3PM', '4PM'];
        @endphp

        <div class="modal-header-top">
            <button type="button" class="manage-btn" onclick="toggleManageMode()">
                <i class="fa-solid fa-pencil"></i> Manage
            </button>
            <div class="calendar-navigation">
                <a href="{{ request()->fullUrlWithQuery(['date' => $prevWeekDate]) }}" class="nav-arrow">&laquo;</a>
                <h2 class="month-title">{{ $startOfWeek->format('M d') }} - {{ $startOfWeek->copy()->addDays(4)->format('M d, Y') }}</h2>
                <a href="{{ request()->fullUrlWithQuery(['date' => $nextWeekDate]) }}" class="nav-arrow">&raquo;</a>
            </div>
            <button class="close-btn" onclick="closeAdminModal()">&times;</button>
        </div>

        <table class="schedule-grid">
            <thead>
                <tr>
                    <th class="time-col"></th>
                    @foreach($calendarDays as $day)
                        <th>{{ $day->format('D d') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($timeSlots as $time)
                    <tr>
                        <td class="time-col">{{ $time }}</td>
                        @foreach($calendarDays as $day)
                            @php
                                $cellKey = $day->format('Y-m-d') . '|' . $time;
                                $cellStatus = $scheduleRows[$cellKey] ?? 'available';
                                $cellClass = ['available' => 'cell-white', 'booked' => 'cell-green', 'class' => 'cell-red', 'leave' => 'cell-grey'][$cellStatus] ?? 'cell-white';
                            @endphp
                            <td class="{{ $cellClass }}" 
                                data-date="{{ $day->format('Y-m-d') }}" 
                                data-time="{{ $time }}">
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="legend">
            <div class="legend-item"><span class="cell-white"></span>Available</div>
            <div class="legend-item"><span class="cell-green"></span>Booked</div>
            <div class="legend-item"><span class="cell-red"></span>Class Hours</div>
            <div class="legend-item"><span class="cell-grey"></span>On Leave</div>
        </div>
    </div>
</div>

<script>
    let currentTeacherId = null;
    let isManageMode = false;

    // We now accept the teacherId when opening the modal
    function openAdminModal(section, name, teacherId) {
        if (!teacherId || teacherId === 'null') {
            alert('No teacher assigned for this section.');
            return;
        }

        currentTeacherId = Number(teacherId);
        if (Number.isNaN(currentTeacherId)) {
            alert('Invalid teacher ID. Please assign a valid teacher first.');
            return;
        }

        document.getElementById('adminCalendarModal').classList.remove('hidden');

        // Attach the teacherId to the browser URL so refresh and reload preserve the opened teacher.
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.set('teacher_id', currentTeacherId);
        window.history.replaceState({}, document.title, newUrl.toString());

        document.querySelectorAll('.nav-arrow').forEach(arrow => {
            let url = new URL(arrow.href);
            url.searchParams.set('teacher_id', currentTeacherId);
            arrow.href = url.toString();
        });

        loadTeacherSchedule(currentTeacherId);
    }
    
    function closeAdminModal() { 
        document.getElementById('adminCalendarModal').classList.add('hidden'); 
        
        // Reset manage mode when closing so the button goes back to "Manage"
        isManageMode = false;
        const btn = document.querySelector('.manage-btn');
        btn.innerHTML = '<i class="fa-solid fa-pencil"></i> Manage';
        btn.style.color = 'var(--ma-green)';
        
        // Remove the click listeners and pointer cursors from cells
        const cells = document.querySelectorAll('.schedule-grid td:not(.time-col)');
        cells.forEach(cell => {
            cell.style.cursor = 'default';
            cell.onclick = null; 
        });

        // NEW: Wipe the URL clean when closing the modal so it resets for the next teacher
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // If the page reloaded from an arrow click, grab the teacher ID from the URL
        if (urlParams.has('teacher_id')) {
            currentTeacherId = urlParams.get('teacher_id');
            document.getElementById('adminCalendarModal').classList.remove('hidden');
            
            document.querySelectorAll('.nav-arrow').forEach(arrow => {
                let url = new URL(arrow.href);
                url.searchParams.set('teacher_id', currentTeacherId);
                arrow.href = url.toString();
            });

            loadTeacherSchedule(currentTeacherId);
        }
    };

    function toggleManageMode() {
        const cells = document.querySelectorAll('.schedule-grid td:not(.time-col)');
        const btn = document.querySelector('.manage-btn');

        if (!isManageMode) {
            // ENTER MANAGE MODE: Change button to 'Save Changes' and make cells clickable
            isManageMode = true;
            btn.innerText = 'Save Changes';
            btn.style.color = 'var(--ma-red)';
            btn.insertAdjacentHTML('afterbegin', '<i class="fa-solid fa-check"></i> ');
            
            cells.forEach(cell => {
                cell.style.cursor = 'pointer';
                cell.onclick = function() {
                    if (this.classList.contains('cell-white')) {
                        this.classList.replace('cell-white', 'cell-green');
                    } else if (this.classList.contains('cell-green')) {
                        this.classList.replace('cell-green', 'cell-red');
                    } else if (this.classList.contains('cell-red')) {
                        this.classList.replace('cell-red', 'cell-grey');
                    } else {
                        this.classList.remove('cell-grey');
                        this.classList.add('cell-white');
                    }
                };
            });
        } else {
            // THEY CLICKED SAVE: Execute the save function!
            saveScheduleToDatabase();
        }
    }

    function loadTeacherSchedule(teacherId) {
        fetch("{{ route('appointments.getAvailability') }}?teacher_id=" + encodeURIComponent(teacherId), {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cells = document.querySelectorAll('.schedule-grid td:not(.time-col)');
                cells.forEach(cell => {
                    const date = cell.getAttribute('data-date');
                    const time = cell.getAttribute('data-time');
                    const match = data.schedules.find(item => item.date === date && item.time === time);
                    cell.classList.remove('cell-white', 'cell-green', 'cell-red', 'cell-grey');

                    if (!match || match.status === 'available') {
                        cell.classList.add('cell-white');
                    } else if (match.status === 'booked') {
                        cell.classList.add('cell-green');
                    } else if (match.status === 'class' || match.status === 'class_hours') {
                        cell.classList.add('cell-red');
                    } else if (match.status === 'leave' || match.status === 'on_leave') {
                        cell.classList.add('cell-grey');
                    } else {
                        cell.classList.add('cell-white');
                    }
                });
            } else {
                console.error('Failed to load teacher schedule:', data);
            }
        })
        .catch(error => {
            console.error('Error loading teacher schedule:', error);
        });
    }

    function saveScheduleToDatabase() {
        if (!currentTeacherId || Number.isNaN(currentTeacherId)) {
            alert("No teacher selected! Wait for a teacher to be assigned to this section before setting their schedule.");
            return;
        }

        let scheduleData = []; 
        const cells = document.querySelectorAll('.schedule-grid td:not(.time-col)');

        // Loop through every box and collect its date, time, and color status
        cells.forEach(cell => {
            let cellStatus = 'available'; 
            
            if (cell.classList.contains('cell-green')) cellStatus = 'booked';
            if (cell.classList.contains('cell-red')) cellStatus = 'class';
            if (cell.classList.contains('cell-grey')) cellStatus = 'leave';

            scheduleData.push({
                date: cell.getAttribute('data-date'),
                time: cell.getAttribute('data-time'),
                status: cellStatus
            });
        });

        console.log('Saving schedule', { currentTeacherId, count: scheduleData.length, scheduleData });

        fetch("{{ route('appointments.updateAvailability') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                teacher_id: currentTeacherId,
                schedules: scheduleData
            })
        })
        .then(async response => {
            const text = await response.text();
            if (!response.ok) {
                console.error('Server returned non-OK:', response.status, response.statusText, text);
                throw new Error('HTTP ' + response.status + ' ' + response.statusText + ': ' + text);
            }
            try {
                return JSON.parse(text);
            } catch (parseError) {
                console.error('Failed to parse JSON:', parseError, text);
                throw new Error('Invalid JSON response: ' + text);
            }
        })
        .then(data => {
            if (data.success) {
                alert('Schedule saved successfully!');
                closeAdminModal(); 
            } else {
                console.error('Save failed response:', data);
                alert('Save failed: ' + (data.message || JSON.stringify(data)));
            }
        })
        .catch(error => {
            console.error('Error saving schedule:', error);
            alert('Something went wrong. Check the browser console.');
        });
    }
</script>
@endsection