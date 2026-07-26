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
    .header-controls { display: flex; align-items: center; gap: 15px; }
    .calendar-navigation { display: flex; align-items: center; gap: 15px; }
    .nav-arrow { color: var(--ma-orange); font-size: 32px; text-decoration: none; font-weight: 900; cursor: pointer; }
    .month-title { font-size: 24px; font-weight: 900; margin: 0; min-width: 300px; text-align: center; }
    
    .manage-btn { color: var(--ma-green); font-size: 20px; font-weight: 900; background: none; border: none; cursor: pointer; }
    .leave-btn { color: var(--ma-dark-grey); font-size: 20px; font-weight: 900; background: none; border: none; cursor: pointer; }
    .close-btn { background: var(--ma-red); color: white; border: 3px solid #fff; border-radius: 50%; width: 45px; height: 45px; font-size: 28px; cursor: pointer; }

    .schedule-grid { width: 100%; border-collapse: collapse; text-align: center; border: 2px solid #000; }
    .schedule-grid th, .schedule-grid td { border: 2px solid #000; padding: 5px; height: 40px; font-size: 13px; font-weight: 900; }
    .schedule-grid th { background-color: var(--ma-bg-grey); }
    .time-col { background-color: var(--ma-bg-grey); width: 80px; }
    
    .cell-red { background-color: var(--ma-red); }
    .cell-green { background-color: var(--ma-green); }
    .cell-white { background-color: #ffffff; }
    .cell-grey { background-color: var(--ma-dark-grey); }

    /* Custom Toast Message */
    .toast-banner {
        position: absolute;
        top: 15px;
        left: 50%;
        transform: translateX(-50%);
        background: #000;
        color: #fff;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 900;
        font-size: 14px;
        z-index: 1010;
        transition: opacity 0.3s ease;
    }

    .schedule-grid td.active-manage-cell { outline: 3px dashed #000; outline-offset: -3px; }
    .day-header-active { background-color: var(--ma-dark-grey) !important; color: #fff; cursor: pointer; }

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
        <div id="toastBanner" class="toast-banner hidden">Schedule saved successfully!</div>

        @php
            $dateParam = request('date');
            if ($dateParam) {
                $currentDate = \Carbon\Carbon::parse($dateParam);
            } else {
                $currentDate = \Carbon\Carbon::now();
                // Auto-skip to upcoming Monday if today is Saturday or Sunday
                if ($currentDate->isWeekend()) {
                    $currentDate = $currentDate->next(\Carbon\Carbon::MONDAY);
                }
            }

            $startOfWeek = $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            $prevWeekDate = $startOfWeek->copy()->subWeek()->format('Y-m-d');
            $nextWeekDate = $startOfWeek->copy()->addWeek()->format('Y-m-d');
            
            $calendarDays = [];
            for ($i = 0; $i < 5; $i++) { $calendarDays[] = $startOfWeek->copy()->addDays($i); }
            $timeSlots = ['8AM', '9AM', '10AM', '11AM', '1PM', '2PM', '3PM', '4PM'];
        @endphp

        <div class="modal-header-top">
            <div class="header-controls">
                <button type="button" class="manage-btn" onclick="toggleManageMode()">
                    <i class="fa-solid fa-pencil"></i> Manage
                </button>
                <button type="button" class="leave-btn" onclick="toggleLeaveMode()">
                    <i class="fa-solid fa-user-slash"></i> Mark Leave
                </button>
            </div>
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
                        <th class="day-header" data-date="{{ $day->format('Y-m-d') }}">{{ $day->format('D d') }}</th>
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
    let isLeaveMode = false;
    let selectedCell = null;
    
    const statusClasses = ['cell-white', 'cell-green', 'cell-red', 'cell-grey'];

    function openAdminModal(section, name, teacherId) {
        if (!teacherId || teacherId === 'null') {
            showToast('No teacher assigned for this section.', true);
            return;
        }

        currentTeacherId = Number(teacherId);
        if (Number.isNaN(currentTeacherId)) {
            showToast('Invalid teacher ID.', true);
            return;
        }

        document.getElementById('adminCalendarModal').classList.remove('hidden');

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
        resetControlModes();
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
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

    function showToast(message, isError = false) {
        const toast = document.getElementById('toastBanner');
        toast.innerText = message;
        toast.style.background = isError ? 'var(--ma-red)' : '#000';
        toast.classList.remove('hidden');
        
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 2000);
    }

    function resetControlModes() {
        isManageMode = false;
        isLeaveMode = false;

        const manageBtn = document.querySelector('.manage-btn');
        manageBtn.innerHTML = '<i class="fa-solid fa-pencil"></i> Manage';
        manageBtn.style.color = 'var(--ma-green)';

        const leaveBtn = document.querySelector('.leave-btn');
        leaveBtn.innerHTML = '<i class="fa-solid fa-user-slash"></i> Mark Leave';
        leaveBtn.style.color = 'var(--ma-dark-grey)';

        deselectCurrentCell();
        clearCellListeners();
        clearDayHeaderListeners();
    }

    function changeCellColor(cell, direction = 'next') {
        let currentClass = statusClasses.find(cls => cell.classList.contains(cls)) || 'cell-white';
        let currentIndex = statusClasses.indexOf(currentClass);
        
        if (direction === 'next') {
            currentIndex = (currentIndex + 1) % statusClasses.length;
        } else if (direction === 'prev') {
            currentIndex = (currentIndex - 1 + statusClasses.length) % statusClasses.length;
        }
        
        cell.classList.remove(...statusClasses);
        cell.classList.add(statusClasses[currentIndex]);
    }

    function selectCell(cell) {
        deselectCurrentCell();
        selectedCell = cell;
        selectedCell.classList.add('active-manage-cell');
    }

    function deselectCurrentCell() {
        if (selectedCell) {
            selectedCell.classList.remove('active-manage-cell');
            selectedCell = null;
        }
    }

    function clearCellListeners() {
        const cells = document.querySelectorAll('.schedule-grid td:not(.time-col)');
        cells.forEach(cell => {
            cell.style.cursor = 'default';
            cell.onclick = null;
        });
    }

    function clearDayHeaderListeners() {
        const headers = document.querySelectorAll('.day-header');
        headers.forEach(header => {
            header.classList.remove('day-header-active');
            header.style.cursor = 'default';
            header.onclick = null;
        });
    }

    function toggleManageMode() {
        const manageBtn = document.querySelector('.manage-btn');
        if (isLeaveMode) resetControlModes();

        if (!isManageMode) {
            isManageMode = true;
            manageBtn.innerText = 'Save Changes';
            manageBtn.style.color = 'var(--ma-red)';
            manageBtn.insertAdjacentHTML('afterbegin', '<i class="fa-solid fa-check"></i> ');
            
            const cells = document.querySelectorAll('.schedule-grid td:not(.time-col)');
            cells.forEach(cell => {
                cell.style.cursor = 'pointer';
                cell.onclick = function() {
                    selectCell(this);
                    changeCellColor(this, 'next');
                };
            });
        } else {
            resetControlModes();
            saveScheduleToDatabase();
        }
    }

    function toggleLeaveMode() {
        const leaveBtn = document.querySelector('.leave-btn');
        if (isManageMode) resetControlModes();

        if (!isLeaveMode) {
            isLeaveMode = true;
            leaveBtn.innerText = 'Save Leave';
            leaveBtn.style.color = 'var(--ma-red)';
            leaveBtn.insertAdjacentHTML('afterbegin', '<i class="fa-solid fa-check"></i> ');

            const headers = document.querySelectorAll('.day-header');
            headers.forEach(header => {
                header.classList.add('day-header-active');
                header.onclick = function() {
                    toggleWholeDayLeave(this.getAttribute('data-date'));
                };
            });
        } else {
            resetControlModes();
            saveScheduleToDatabase();
        }
    }

    function toggleWholeDayLeave(dateStr) {
        const dayCells = document.querySelectorAll(`.schedule-grid td[data-date="${dateStr}"]`);
        let allGrey = Array.from(dayCells).every(cell => cell.classList.contains('cell-grey'));

        dayCells.forEach(cell => {
            cell.classList.remove(...statusClasses);
            cell.classList.add(allGrey ? 'cell-white' : 'cell-grey');
        });
    }

    document.addEventListener('keydown', function(event) {
        if (!isManageMode || !selectedCell) return;
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            changeCellColor(selectedCell, 'next');
        } else if (event.key === 'ArrowLeft') {
            event.preventDefault();
            changeCellColor(selectedCell, 'prev');
        }
    });

    function loadTeacherSchedule(teacherId) {
        fetch("{{ route('appointments.getAvailability') }}?teacher_id=" + encodeURIComponent(teacherId), {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
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
            }
        })
        .catch(error => console.error('Error loading schedule:', error));
    }

    function saveScheduleToDatabase() {
        if (!currentTeacherId || Number.isNaN(currentTeacherId)) {
            showToast("No teacher selected!", true);
            return;
        }

        let scheduleData = []; 
        const cells = document.querySelectorAll('.schedule-grid td:not(.time-col)');

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
            if (!response.ok) throw new Error(text);
            return JSON.parse(text);
        })
        .then(data => {
            if (data.success) {
                showToast('Schedule saved successfully!');
            } else {
                showToast('Save failed: ' + (data.message || 'Unknown error'), true);
            }
        })
        .catch(error => {
            console.error('Error saving schedule:', error);
            showToast('Something went wrong. Check browser console.', true);
        });
    }
</script>
@endsection