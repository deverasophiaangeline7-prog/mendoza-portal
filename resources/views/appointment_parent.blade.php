@extends('layouts.navigation')

@section('title', 'Appointment Scheduling (Parent)')

@section('content')
<style>
    :root {
        --ma-red: #d00101;
        --ma-orange: #ffaa00; 
        --ma-green: #34a853;
        --ma-bg-grey: #e8e8e8;
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

    /* Standardized Action Buttons */
    .btn-flat {
        padding: 8px 18px;
        border: 2px solid #000;
        border-radius: 20px;
        font-weight: 900;
        font-size: 14px;
        cursor: pointer;
        color: black; /* Default to black text to match Canva layout */
    }

    .btn-approve { background-color: var(--ma-green); }
    .btn-decline { background-color: var(--ma-red); color: white; }
    .btn-reschedule { background-color: var(--ma-orange); } 

    /* Calendar */
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

    /* Matrix */
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
    .cell-half-top { background: linear-gradient(180deg, var(--ma-green) 0 50%, #ffffff 50% 100%); }
    .cell-half-bottom { background: linear-gradient(180deg, #ffffff 0 50%, var(--ma-green) 50% 100%); }
    .cell-white { background-color: #ffffff; }

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

    /* Reschedule Modal CSS */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;
    }
    .hidden { display: none !important; }
    
    .nested-modal-content {
        background: white; border: 4px solid #000; padding: 30px; width: 60%; max-width: 600px; text-align: center; box-shadow: 10px 10px 0px var(--ma-orange); border-radius: 15px;
    }
    .nested-modal-content h3 { margin-top: 0; font-size: 20px; font-weight: 900;}
    .reason-input {
        width: 90%; padding: 15px; border: 2px solid #000; border-radius: 10px; background: var(--ma-bg-grey); margin-bottom: 25px; font-size: 16px; font-weight: bold;
    }
    .nested-modal-actions { display: flex; justify-content: center; gap: 20px; }
</style>

<div class="dashboard-container">
    <div class="main-content">
        
        <!-- Left Side: Forms and Statuses -->
        <div class="left-column">
            
            <div class="ma-card">
                <h3>Appoint with your adviser</h3>
                <form action="{{ route('appointments.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Discussion Topic</label>
                        <input type="text" name="discussion_topic" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Appointment Date</label>
                        <input type="date" name="appointment_date" class="form-control" required>
                    </div>
                    <div class="time-group">
                        <div class="form-group" style="flex: 1;">
                            <label>Start Time</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>End Time</label>
                            <input type="time" name="end_time" class="form-control" required>
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

            <!-- MY REQUESTS LIST -->
            <div class="ma-card">
                <h3>My Requests</h3>

                @if($incomingRequests->isEmpty() && $mySentRequests->isEmpty())
                    <div class="incoming-data-row">
                        <div style="text-align: center; width: 100%;">No appointment requests yet.</div>
                    </div>
                @else
                    
                    <!-- 1. Incoming Requests (Needs Action) -->
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

                    <!-- 2. My Sent Requests (Waiting for Teacher) -->
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

        <!-- Right Side: Schedule Matrix -->
        <div class="right-column">
            @php
                $dateParam = request('date', \Carbon\Carbon::now()->format('Y-m-d'));
                $currentDate = \Carbon\Carbon::parse($dateParam);
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
                                    if ($slot) {
                                        if ($slot->status === 'class') $cellClass = 'cell-red';
                                        elseif ($slot->status === 'leave') $cellClass = 'cell-grey';
                                        elseif ($slot->status === 'booked') $cellClass = 'cell-green';
                                        elseif ($slot->status === 'booked-half') $cellClass = 'cell-half-top';
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
                                <td class="{{ $cellClass }}" title="{{ $meetingTooltip }}"></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="legend">
                <div class="legend-item"><span class="cell-white"></span>Available</div>
                <div class="legend-item"><span class="cell-green"></span>Booked</div>
                <div class="legend-item"><span class="cell-red"></span>Class Hours</div>
                <div class="legend-item"><span style="background-color: grey;"></span>On Leave</div>
            </div>
            <p class="disclaimer">Schedules booked on "On Leave" will be pending for reschedule.</p>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODALS SECTION -->
<!-- ============================================== -->

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

    function openRescheduleModal(appointmentId) {
        const form = document.getElementById('rescheduleForm');
        form.action = `/appointments/${appointmentId}/reschedule`;
        openModal('rescheduleModal');
    }
</script>
@endsection