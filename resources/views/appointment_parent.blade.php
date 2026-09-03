@extends('layouts.navigation')

@section('title', 'Appointment Scheduling (Parent)')

@section('content')
<style>
    :root {
        --ma-red: #d00101;
        --ma-orange: #ffaa00;
        --ma-green: #34a853;
        --ma-bg-grey: #e8e8e8;
        --ma-dark-grey: #b0b0b0; 
    }

    .dashboard-container {
        display: flex;
        font-family: 'Arial', sans-serif;
        width: 100%;
        height: 100%;
        overflow: hidden; 
    }

    .main-content {
        flex: 1;
        padding: 20px 30px;
        display: flex;
        gap: 30px;
        background-color: #ffffff;
    }

    .left-column { flex: 1; display: flex; flex-direction: column; gap: 20px; }
    .right-column { flex: 1.2; position: relative; }

    .ma-card {
        border: 2px solid #000;
        border-radius: 25px; 
        padding: 20px;
        background: #fff;
    }

    .ma-card h3 {
        text-align: center;
        margin-top: 0;
        font-size: 20px;
        font-weight: 900;
        margin-bottom: 15px;
    }

    .form-group { margin-bottom: 12px; }
    
    .form-group label {
        display: block;
        font-weight: 900;
        font-size: 13px;
        margin-bottom: 3px;
        margin-left: 5px;
    }

    .form-control {
        width: 100%;
        padding: 8px 15px;
        border: 2px solid #000;
        border-radius: 25px; 
        background-color: var(--ma-bg-grey);
        font-weight: bold;
        font-size: 14px;
        box-sizing: border-box;
    }

    .time-group { display: flex; gap: 15px; }

    .btn-submit {
        background-color: var(--ma-green);
        color: black;
        border: 2px solid #000;
        border-radius: 25px;
        padding: 8px 25px;
        font-weight: 900;
        font-size: 14px;
        display: block;
        margin: 15px auto 0;
        cursor: pointer;
    }

    .status-box-container {
        display: flex;
        border: 2px solid #000;
        border-radius: 4px; 
        background: #fff;
        align-items: stretch;
    }

    .status-label-block {
        background-color: var(--ma-bg-grey);
        padding: 12px 15px;
        font-weight: 900;
        font-size: 15px;
        border-right: 2px solid #000;
        display: flex;
        align-items: center;
    }

    .status-data-block {
        padding: 12px 15px;
        display: flex;
        align-items: center;
        gap: 20px;
        font-weight: 900;
        font-size: 15px;
    }

    .pill-orange {
        background-color: var(--ma-orange);
        color: black;
        border: 2px solid #000;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 900;
        font-size: 14px;
    }

    .incoming-data-row {
        display: flex;
        gap: 5px; 
        margin-bottom: 15px;
    }

    .incoming-data-row div {
        flex: 1;
        background-color: var(--ma-bg-grey);
        padding: 10px;
        text-align: center;
        font-weight: bold;
        font-size: 14px;
        border-radius: 4px;
    }

    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .btn-flat {
        padding: 8px 18px;
        border: 2px solid #000;
        border-radius: 20px;
        font-weight: 900;
        font-size: 14px;
        cursor: pointer;
        color: black;
    }

    .btn-approve { background-color: var(--ma-green); }
    .btn-decline { background-color: var(--ma-red); color: white; }
    .btn-reschedule { background-color: var(--ma-orange); } 

    .calendar-title {
        text-align: center;
        font-size: 24px;
        font-weight: 900;
        margin: 0 0 5px 0;
    }

    .calendar-header-wrapper {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 15px;
        min-height: 40px;
    }

    .calendar-navigation {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .month-title {
        color: var(--ma-orange);
        text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
        font-weight: 900;
        font-size: 32px;
        margin: 0;
    }

    .nav-arrow {
        color: var(--ma-orange);
        text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
        font-weight: 900;
        font-size: 32px;
        text-decoration: none;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .nav-arrow:hover { transform: scale(1.2); color: var(--ma-red); }

    .schedule-grid {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
        border: 2px solid #000;
    }

    .schedule-grid th, .schedule-grid td { border: 2px solid #000; padding: 10px 5px; height: 40px; }
    .schedule-grid th { background-color: var(--ma-bg-grey); font-weight: 900; }
    .day-header { font-size: 11px; text-transform: uppercase; color: #555; }
    .date-header { font-size: 16px; }
    .time-col { background-color: var(--ma-bg-grey); width: 60px; font-weight: 900; font-size: 14px; }
    
    .cell-red { background-color: var(--ma-red); }
    .cell-green { background-color: var(--ma-green); }
    .cell-grey { background-color: var(--ma-dark-grey) !important; }
    .cell-white { background-color: #ffffff; }

    .cell-half-top { background: linear-gradient(180deg, var(--ma-green) 0 50%, #ffffff 50% 100%); }
    .cell-half-bottom { background: linear-gradient(180deg, #ffffff 0 50%, var(--ma-green) 50% 100%); }

    .legend {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 15px;
        font-weight: 900;
        font-size: 14px;
    }
    .legend-item span {
        display: inline-block; width: 16px; height: 16px;
        border-radius: 50%; border: 2px solid #000; margin-right: 5px; vertical-align: middle;
    }
    .disclaimer { color: var(--ma-red); text-align: center; font-size: 13px; font-weight: 900; margin-top: 5px; }

    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;
    }
    .hidden { display: none !important; }
    
    .nested-modal-content, .validation-modal {
        background: white; border: 4px solid #000; padding: 30px; width: 60%; max-width: 600px; text-align: center; box-shadow: 10px 10px 0px var(--ma-orange); border-radius: 15px;
    }

    .validation-modal {
        max-width: 450px;
    }

    .validation-modal h3 {
        margin-top: 0;
        color: var(--ma-red);
        font-weight: 900;
        font-size: 22px;
    }

    .nested-modal-content h3 { margin-top: 0; font-size: 20px; font-weight: 900;}
    .reason-input {
        width: 90%; padding: 15px; border: 2px solid #000; border-radius: 10px; background: var(--ma-bg-grey); margin-bottom: 25px; font-size: 16px; font-weight: bold;
    }
    .nested-modal-actions { display: flex; justify-content: center; gap: 20px; }
</style>

<div class="dashboard-container">
    <div class="main-content">
        
        <div class="left-column">
            
            @if(session('success'))
            <div style="background-color: #d4edda; color: #155724; border: 2px solid #000; border-radius: 15px; padding: 12px 20px; font-weight: bold; display: flex; align-items: center; justify-content: space-between;">
                <span><i class="fa-solid fa-circle-check" style="margin-right: 8px; color: #34a853;"></i> {{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none';" style="background:none; border:none; font-size: 18px; font-weight: bold; cursor: pointer;">&times;</button>
            </div>
            @endif

            <div class="ma-card">
                <h3>Appoint with your adviser</h3>
                <form id="appointmentForm" action="{{ route('appointments.store') }}" method="POST" onsubmit="return validateAppointmentForm(event)">
                    @csrf
                    <div class="form-group">
                        <label>Discussion Topic</label>
                        <input type="text" name="discussion_topic" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Appointment Date</label>
                        <input type="date" id="appointment_date" name="appointment_date" class="form-control" required
                               min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                               max="{{ \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->addWeeks(2)->addDays(4)->format('Y-m-d') }}">
                    </div>
                    <div class="time-group">
                        <div class="form-group" style="flex: 1;">
                            <label>Start Time</label>
                            <input type="time" id="start_time" name="start_time" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>End Time</label>
                            <input type="time" id="end_time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">Submit Request</button>
                </form>
            </div>

            @php
                $activeRequest = $incomingRequests->first() ?? $mySentRequests->first();
                $activeRequestStatus = $activeRequest?->status ?? 'none';
                $statusLabel = ucfirst($activeRequestStatus);
                if ($activeRequestStatus === 'none') {
                    $statusLabel = 'No Requests';
                }
            @endphp

            <div class="status-box-container">
                <div class="status-label-block">Request Status:</div>
                <div class="status-data-block">
                    <span>{{ $statusLabel }}</span>
                    @if($activeRequestStatus !== 'none')
                        <span class="pill-orange">{{ $activeRequestStatus === 'reschedule' ? 'Reschedule' : 'Active' }}</span>
                    @endif
                </div>
            </div>

            <div class="ma-card">
                <h3>My Requests</h3>

                @if($incomingRequests->isEmpty() && $mySentRequests->isEmpty())
                    <div class="incoming-data-row">
                        <div style="text-align: center; width: 100%;">No appointment requests yet.</div>
                    </div>
                @else
                    
                    @foreach($incomingRequests as $request)
                        <div style="margin-bottom: 20px;">
                            <div style="text-align: left; font-weight: 900; font-size: 13px; margin-bottom: 5px; color: var(--ma-red); margin-left: 5px;">
                                Action Required (From Teacher)
                            </div>
                            <div class="incoming-data-row">
                                <div>{{ $request->discussion_topic }}</div>
                                <div>{{ \Carbon\Carbon::parse($request->appointment_date)->format('M j') }}, {{ \Carbon\Carbon::parse($request->start_time)->format('g:iA') }}</div>
                            </div>
                            <div class="action-buttons">
                                <form action="{{ route('appointments.approve', $request->id) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn-flat btn-approve">Approve</button>
                                </form>
                                <form action="{{ route('appointments.decline', $request->id) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn-flat btn-decline">Decline</button>
                                </form>
                                <button type="button" class="btn-flat btn-reschedule" onclick="openRescheduleModal({{ $request->id }})">Reschedule</button>
                            </div>
                        </div>

                        @if(!$loop->last || $mySentRequests->isNotEmpty())
                            <hr style="border: 1px dashed #ccc; margin-bottom: 15px;">
                        @endif
                    @endforeach

                    @foreach($mySentRequests as $request)
                        <div style="margin-bottom: 20px;">
                            <div style="text-align: left; font-weight: 900; font-size: 13px; margin-bottom: 5px; margin-left: 5px;">
                                Sent to Teacher
                            </div>
                            <div class="incoming-data-row">
                                <div>{{ $request->discussion_topic }}</div>
                                <div>{{ \Carbon\Carbon::parse($request->appointment_date)->format('M j') }}, {{ \Carbon\Carbon::parse($request->start_time)->format('g:iA') }}</div>
                            </div>
                            <div class="action-buttons">
                                <span class="pill-orange">{{ $request->status === 'reschedule' ? 'Reschedule' : ucfirst($request->status) }}</span>
                            </div>
                        </div>

                        @if(!$loop->last)
                            <hr style="border: 1px dashed #ccc; margin-bottom: 15px;">
                        @endif
                    @endforeach

                @endif
            </div>

        </div>

        <div class="right-column">
            @php
                $dateParam = request('date');
                if ($dateParam) {
                    $currentDate = \Carbon\Carbon::parse($dateParam);
                } else {
                    $currentDate = \Carbon\Carbon::now();
                    if ($currentDate->isWeekend()) {
                        $currentDate = $currentDate->next(\Carbon\Carbon::MONDAY);
                    }
                }

                $startOfWeek = $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                $prevWeekDate = $startOfWeek->copy()->subWeek()->format('Y-m-d');
                $nextWeekDate = $startOfWeek->copy()->addWeek()->format('Y-m-d');
                
                $weekDays = [];
                for ($i = 0; $i < 5; $i++) {
                    $weekDays[] = $startOfWeek->copy()->addDays($i);
                }
                $timeSlots = ['8AM', '9AM', '10AM', '11AM', '1PM', '2PM', '3PM', '4PM'];
            @endphp

            <h2 class="calendar-title">{{ $adviserName ? 'Adviser ' . strtoupper($adviserName) . ' Schedule' : 'Adviser Schedule' }}</h2>
            
            <div class="calendar-header-wrapper">
                <div class="calendar-navigation">
                    <a href="{{ request()->url() }}?date={{ $prevWeekDate }}" class="nav-arrow">&laquo;</a>
                    <h2 class="month-title">{{ $currentDate->format('F Y') }}</h2>
                    <a href="{{ request()->url() }}?date={{ $nextWeekDate }}" class="nav-arrow">&raquo;</a>
                </div>
            </div>

            <table class="schedule-grid">
                <thead>
                    <tr>
                        <th class="time-col"></th>
                        @foreach($weekDays as $day)
                            <th>
                                <div class="day-header">{{ $day->format('D') }}</div>
                                <div class="date-header">{{ $day->format('j') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeSlots as $time)
                        <tr>
                            <td class="time-col">{{ $time }}</td>
                            @foreach($weekDays as $day)
                                @php
                                    $slot = $adviserSchedule->first(function ($schedule) use ($day, $time) {
                                        return $schedule->date === $day->format('Y-m-d') && $schedule->time_slot === $time;
                                    });
                                    $cellClass = 'cell-white';
                                    $statusValue = 'available';

                                    if ($slot) {
                                        if ($slot->status === 'class' || $slot->status === 'class_hours') {
                                            $cellClass = 'cell-red';
                                            $statusValue = 'class';
                                        } elseif ($slot->status === 'leave' || $slot->status === 'on_leave') {
                                            $cellClass = 'cell-grey';
                                            $statusValue = 'leave';
                                        } elseif ($slot->status === 'booked') {
                                            $cellClass = 'cell-green';
                                            $statusValue = 'booked';
                                        } elseif ($slot->status === 'booked-half') {
                                            $cellClass = 'cell-half-top';
                                            $statusValue = 'booked';
                                        }
                                    }
                                    
                                    $cellStartTime = \Carbon\Carbon::parse($day->format('Y-m-d') . ' ' . $time);
                                    $cellEndTime = $cellStartTime->copy()->addHour();

                                    $meetingTooltip = '';
                                    $meeting = $bookedAppointments->first(function ($appointment) use ($cellStartTime, $cellEndTime) {
                                        $appStart = \Carbon\Carbon::parse($appointment->appointment_date . ' ' . $appointment->start_time);
                                        $appEnd = \Carbon\Carbon::parse($appointment->appointment_date . ' ' . $appointment->end_time);
                                        return $appStart->lt($cellEndTime) && $appEnd->gt($cellStartTime);
                                    });

                                    if ($meeting) {
                                        $statusValue = 'booked';
                                        $parentName = strtoupper(optional($meeting->parent->student)->first_name . ' ' . optional($meeting->parent->student)->last_name ?: optional($meeting->parent)->username);
                                        $meetingTooltip = $parentName . ' • ' . $meeting->discussion_topic . ' • ' . \Carbon\Carbon::parse($meeting->start_time)->format('g:iA') . ' - ' . \Carbon\Carbon::parse($meeting->end_time)->format('g:iA');
                                        
                                        $appStart = \Carbon\Carbon::parse($meeting->appointment_date . ' ' . $meeting->start_time);
                                        $appEnd = \Carbon\Carbon::parse($meeting->appointment_date . ' ' . $meeting->end_time);
                                        $overlapStart = $appStart->max($cellStartTime);
                                        $overlapEnd = $appEnd->min($cellEndTime);
                                        $durationInCell = $overlapStart->diffInMinutes($overlapEnd);

                                        if ($durationInCell >= 60) {
                                            $cellClass = 'cell-green';
                                        } elseif ($durationInCell <= 30) {
                                            if ($overlapStart->minute >= 30) {
                                                $cellClass = 'cell-half-bottom';
                                            } else {
                                                $cellClass = 'cell-half-top';
                                            }
                                        }
                                    }
                                @endphp
                                <td class="{{ $cellClass }}" 
                                    data-date="{{ $day->format('Y-m-d') }}" 
                                    data-time="{{ $time }}" 
                                    data-status="{{ $statusValue }}" 
                                    title="{{ $meetingTooltip }}">
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
            <p class="disclaimer">Schedules booked on "On Leave" will be pending for reschedule.</p>
        </div>
    </div>
</div>

<!-- VALIDATION POPUP MODAL -->
<div id="validationModalOverlay" class="modal-overlay hidden">
    <div class="validation-modal">
        <h3 id="valModalTitle"><i class="fa-solid fa-triangle-exclamation"></i> Invalid Action</h3>
        <p id="valModalMessage" style="font-weight: bold; font-size: 15px; margin: 20px 0; color: #333;"></p>
        <button type="button" class="btn-submit" onclick="closeModal('validationModalOverlay')" style="margin: 0 auto;">OK</button>
    </div>
</div>

<!-- Reschedule Modal -->
<div id="rescheduleModal" class="modal-overlay hidden">
    <div class="nested-modal-content">
        <h3>State your reason for rescheduling</h3>
        
        <form id="rescheduleForm" method="POST" action="">
            @csrf @method('PATCH')
            <input type="text" name="reason" class="reason-input" required>
            
            <div class="nested-modal-actions">
                <button type="button" class="btn-flat btn-decline" onclick="closeModal('rescheduleModal')" style="padding: 10px 30px;">Cancel</button>
                <button type="submit" class="btn-flat btn-approve" style="padding: 10px 30px;">Send</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function showValidationPopUp(message) {
        document.getElementById('valModalMessage').innerText = message;
        openModal('validationModalOverlay');
    }

    function validateAppointmentForm(event) {
        const dateInput = document.getElementById('appointment_date').value;
        const startTimeInput = document.getElementById('start_time').value;
        const endTimeInput = document.getElementById('end_time').value;

        if (!dateInput || !startTimeInput || !endTimeInput) return true;

        const today = "{{ \Carbon\Carbon::now()->format('Y-m-d') }}";
        const maxDate = "{{ \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->addWeeks(2)->addDays(4)->format('Y-m-d') }}";
        
        if (dateInput < today) {
            event.preventDefault();
            showValidationPopUp('Appointments cannot be booked in the past.');
            return false;
        }

        if (dateInput > maxDate) {
            event.preventDefault();
            showValidationPopUp('Appointments can only be scheduled up to two weeks in advance.');
            return false;
        }

        const [year, month, day] = dateInput.split('-');
        const dateObj = new Date(year, month - 1, day);
        const dayOfWeek = dateObj.getDay(); 
        if (dayOfWeek === 0 || dayOfWeek === 6) { 
            event.preventDefault();
            showValidationPopUp('Appointments are only available from Monday to Friday.');
            return false;
        }

        const startParts = startTimeInput.split(':');
        const endParts = endTimeInput.split(':');

        const startMins = parseInt(startParts[0], 10) * 60 + parseInt(startParts[1], 10);
        const endMins = parseInt(endParts[0], 10) * 60 + parseInt(endParts[1], 10);

        const duration = endMins - startMins;

        if (duration <= 0) {
            event.preventDefault();
            showValidationPopUp('Invalid time selected. The end time must be later than the start time.');
            return false;
        }

        if (duration !== 30 && duration !== 60) {
            event.preventDefault();
            showValidationPopUp('Appointment duration must be exactly 30 minutes or 1 hour.');
            return false;
        }

        const cellHour = parseInt(startParts[0], 10);
        let timeSlotLabel = '';

        if (cellHour === 8) timeSlotLabel = '8AM';
        else if (cellHour === 9) timeSlotLabel = '9AM';
        else if (cellHour === 10) timeSlotLabel = '10AM';
        else if (cellHour === 11) timeSlotLabel = '11AM';
        else if (cellHour === 13) timeSlotLabel = '1PM';
        else if (cellHour === 14) timeSlotLabel = '2PM';
        else if (cellHour === 15) timeSlotLabel = '3PM';
        else if (cellHour === 16) timeSlotLabel = '4PM';

        if (timeSlotLabel) {
            const matchingCell = document.querySelector(`td[data-date="${dateInput}"][data-time="${timeSlotLabel}"]`);
            if (matchingCell) {
                const cellStatus = matchingCell.getAttribute('data-status');
                if (cellStatus === 'leave') {
                    event.preventDefault();
                    showValidationPopUp('Cannot book an appointment! The teacher is On Leave for this day.');
                    return false;
                } else if (cellStatus === 'class') {
                    event.preventDefault();
                    showValidationPopUp('Cannot book an appointment! This slot is reserved for Class Hours.');
                    return false;
                } else if (cellStatus === 'booked') {
                    event.preventDefault();
                    showValidationPopUp('Cannot book an appointment! This time slot is already Booked.');
                    return false;
                }
            }
        }

        return true;
    }

    function openRescheduleModal(appointmentId) {
        const form = document.getElementById('rescheduleForm');
        form.action = `/appointments/${appointmentId}/reschedule`;
        openModal('rescheduleModal');
    }

    window.onclick = function(event) {
        const valOverlay = document.getElementById('validationModalOverlay');
        const resOverlay = document.getElementById('rescheduleModal');
        if (event.target === valOverlay) closeModal('validationModalOverlay');
        if (event.target === resOverlay) closeModal('rescheduleModal');
    }
</script>
@endsection