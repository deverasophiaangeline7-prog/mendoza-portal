@extends('layouts.navigation')

@section('title', 'Appointment Scheduling')

@section('content')
<style>
    :root {
        --ma-red: #d00101;
        --ma-orange: #ffaa00; /* Bright orange matching Canva */
        --ma-green: #34a853;
        --ma-bg-grey: #e8e8e8;
    }

    .dashboard-container {
        display: flex;
        font-family: 'Arial', sans-serif;
        width: 100%;
        height: 100%;
        overflow: hidden; /* Prevents page scroll */
    }

    /* Main Content Styling */
    .main-content {
        flex: 1;
        padding: 20px 30px;
        display: flex;
        gap: 30px;
        background-color: #ffffff;
    }

    .left-column { flex: 1; display: flex; flex-direction: column; gap: 20px; }
    .right-column { flex: 1.2; position: relative; }

    /* ----------------------------------
       LEFT COLUMN STYLES
       ---------------------------------- */

    /* Form Card */
    .appointment-form-card {
        border: 2px solid #000;
        border-radius: 25px; /* Pill/Card shape */
        padding: 20px;
        background: #fff;
    }

    .appointment-form-card h3 {
        text-align: center;
        margin-top: 0;
        font-size: 20px;
        font-weight: 900;
        margin-bottom: 15px;
    }

    .form-group {
        margin-bottom: 12px;
    }
    
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
        border-radius: 25px; /* Pill shape inputs */
        background-color: var(--ma-bg-grey);
        font-weight: bold;
        font-size: 14px;
        box-sizing: border-box;
    }

    .time-group {
        display: flex;
        gap: 15px;
    }

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

    /* Pending Requests Mini Table */
    .pending-table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid #000;
    }

    .pending-table th {
        background-color: var(--ma-orange);
        color: black;
        padding: 10px;
        border: 2px solid #000;
        font-weight: 900;
        font-size: 15px;
    }

    .pending-table td {
        padding: 12px;
        border: 2px solid #000;
        font-weight: bold;
        background-color: white;
        font-size: 14px;
    }

    /* ----------------------------------
       RIGHT COLUMN STYLES (CALENDAR)
       ---------------------------------- */

    .calendar-title {
        text-align: center;
        font-size: 24px;
        font-weight: 900;
        margin: 0 0 5px 0;
    }

    /* Calendar Header & Navigation */
    .calendar-header-wrapper {
        position: relative;
        display: flex;
        justify-content: center; /* Centers the arrows and month */
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

    .nav-arrow:hover {
        transform: scale(1.2); 
        color: var(--ma-red); 
    }

    /* Requests Icon Trigger */
    .requests-trigger {
        position: absolute;
        right: 0;
        top: -15px; /* Aligns it nicely with the header */
        text-align: center;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .icon-container {
        position: relative;
        font-size: 32px;
        color: #000;
    }

    .request-badge {
        position: absolute;
        top: -8px;
        right: -15px;
        background-color: #ffcc00;
        color: #000;
        border: 2px solid #000;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        font-size: 14px;
        font-weight: 900;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .request-label {
        font-weight: 900;
        font-size: 14px;
        margin-top: 5px;
    }

    /* Schedule Grid */
    .schedule-grid {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
        border: 2px solid #000;
    }

    .schedule-grid th, .schedule-grid td {
        border: 2px solid #000;
        padding: 10px 5px;
        height: 40px;
    }

    .schedule-grid th { background-color: var(--ma-bg-grey); font-weight: 900; }
    
    .day-header { font-size: 11px; text-transform: uppercase; color: #555; }
    .date-header { font-size: 16px; }

    .time-col { background-color: var(--ma-bg-grey); width: 60px; font-weight: 900; font-size: 14px; }
    
    .cell-red { background-color: var(--ma-red); }
    .cell-green { background-color: var(--ma-green); }
    .cell-half-top {
        background: linear-gradient(180deg, var(--ma-green) 0 50%, #ffffff 50% 100%);
        background-repeat: no-repeat;
        background-size: 100% 100%;
    }
    
    .cell-half-bottom {
        background: linear-gradient(180deg, #ffffff 0 50%, var(--ma-green) 50% 100%);
        background-repeat: no-repeat;
        background-size: 100% 100%;
    }
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
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid #000;
        margin-right: 5px;
        vertical-align: middle;
    }

    .disclaimer {
        color: var(--ma-red);
        text-align: center;
        font-size: 13px;
        font-weight: 900;
        margin-top: 5px;
    }

    /* ----------------------------------
       MODALS BASE STYLING
       ---------------------------------- */
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .hidden {
        display: none !important;
    }

    .requests-modal {
        background: white;
        border: 4px solid #000;
        border-radius: 25px;
        width: 80%;
        max-width: 900px;
        padding: 20px;
        position: relative;
    }

    .modal-header {
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        margin-bottom: 20px;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 900;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .close-btn {
        position: absolute;
        right: 0;
        top: 0;
        background: var(--ma-red);
        color: white;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #000;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid #000;
    }

    .modal-table th {
        background-color: var(--ma-orange);
        color: #000;
        border: 2px solid #000;
        padding: 15px;
        font-size: 16px;
    }

    .modal-table td {
        border: 2px solid #000;
        padding: 15px;
        font-weight: bold;
        vertical-align: middle;
    }

    /* Action Buttons in Modal */
    .action-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .btn-flat {
        padding: 8px 18px;
        border: none;
        border-radius: 20px;
        color: white;
        font-weight: 900;
        font-size: 14px;
        cursor: pointer;
    }

    .btn-approve { background-color: var(--ma-green); }
    .btn-decline { background-color: var(--ma-red); }
    .btn-reschedule { background-color: var(--ma-orange); } 

    /* Nested Reschedule Modal */
    .nested-modal {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 20px;
        z-index: 1010;
    }

    .nested-modal-content {
        background: white;
        border: 4px solid #000;
        padding: 30px;
        width: 60%;
        text-align: center;
        box-shadow: 10px 10px 0px var(--ma-orange);
    }

    .nested-modal-content h3 {
        margin-top: 0;
        font-size: 20px;
    }

    .reason-input {
        width: 90%;
        padding: 15px;
        border: 2px solid #000;
        border-radius: 10px;
        background: var(--ma-bg-grey);
        margin-bottom: 25px;
        font-size: 16px;
    }

    .nested-modal-actions {
        display: flex;
        justify-content: center;
        gap: 20px;
    }
</style>

<div class="dashboard-container">
    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Left Side: Form and Mini Pending Requests -->
        <div class="left-column">
            
            <div class="appointment-form-card">
                <h3>Appoint with a parent</h3>
                <form action="{{ route('appointments.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Name</label>
                        <select name="parent_id" class="form-control" required>
                            <option value="">Select Parent</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->user_id }}">
                                    {{ strtoupper(optional($parent->student)->first_name . ' ' . optional($parent->student)->last_name ?: $parent->username) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

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

            <!-- My Sent Requests Table -->
            <table class="pending-table">
                <thead>
                    <tr>
                        <th colspan="2">My Sent Requests</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mySentRequests as $request)
                        <tr>
                            <td>
                                {{ strtoupper(optional($request->parent->student)->first_name . ' ' . optional($request->parent->student)->last_name ?: optional($request->parent)->username) }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($request->appointment_date)->format('M j') }},
                                {{ \Carbon\Carbon::parse($request->start_time)->format('g:iA') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align: center;">No sent requests.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Right Side: Schedule Matrix -->
        <div class="right-column">
            
            @php
                // Check if a date is passed in the URL (e.g., ?date=2026-05-18), otherwise use today
                $dateParam = request('date', \Carbon\Carbon::now()->format('Y-m-d'));
                $currentDate = \Carbon\Carbon::parse($dateParam);
                
                // Find the Monday of the currently viewed week
                $startOfWeek = $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                
                // Calculate the dates for the Previous and Next arrows
                $prevWeekDate = $startOfWeek->copy()->subWeek()->format('Y-m-d');
                $nextWeekDate = $startOfWeek->copy()->addWeek()->format('Y-m-d');
                
                // Generate the 5 days (Mon-Fri)
                $weekDays = [];
                for ($i = 0; $i < 5; $i++) {
                    $weekDays[] = $startOfWeek->copy()->addDays($i);
                }

                $timeSlots = ['8AM', '9AM', '10AM', '11AM', '1PM', '2PM', '3PM', '4PM'];
            @endphp

            <!-- Calendar Header: Title, Navigation, and Requests Icon -->
            <h2 class="calendar-title">My Schedule</h2>
            
            <div class="calendar-header-wrapper">
                
                <!-- Dynamic Calendar Navigation -->
                <div class="calendar-navigation">
                    <a href="{{ request()->url() }}?date={{ $prevWeekDate }}" class="nav-arrow">&laquo;</a>
                    <h2 class="month-title">{{ $currentDate->format('F Y') }}</h2>
                    <a href="{{ request()->url() }}?date={{ $nextWeekDate }}" class="nav-arrow">&raquo;</a>
                </div>

                <!-- Notification Trigger -->
                <div class="requests-trigger" onclick="openModal('requestsModalOverlay')">
                    <div class="icon-container">
                        <i class="fa-solid fa-user-group"></i>
                        <span class="request-badge">{{ $incomingRequests->count() }}</span>
                    </div>
                    <span class="request-label">Requests</span>
                </div>
            </div>

            <!-- Dynamic Matrix matching prototype structure -->
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
                                    // 1. Check predefined statuses in the schedules table
                                    $slot = $schedules->first(function ($schedule) use ($day, $time) {
                                        return $schedule->date === $day->format('Y-m-d') && $schedule->time_slot === $time;
                                    });
                                    
                                    $cellClass = 'cell-white';
                                    if ($slot) {
                                        if ($slot->status === 'class') $cellClass = 'cell-red';
                                        elseif ($slot->status === 'leave') $cellClass = 'cell-grey';
                                        elseif ($slot->status === 'booked') $cellClass = 'cell-green';
                                        elseif ($slot->status === 'booked-half') $cellClass = 'cell-half-top';
                                    }

                                    // 2. Fetch overlapping meetings (handles cross-hour spills)
                                    $cellStartTime = \Carbon\Carbon::parse($day->format('Y-m-d') . ' ' . $time); 
                                    $cellEndTime = $cellStartTime->copy()->addHour(); 

                                    $meetingTooltip = '';
                                    $meeting = $bookedAppointments->first(function ($appointment) use ($cellStartTime, $cellEndTime) {
                                        $appStart = \Carbon\Carbon::parse($appointment->appointment_date . ' ' . $appointment->start_time);
                                        $appEnd = \Carbon\Carbon::parse($appointment->appointment_date . ' ' . $appointment->end_time);
                                        
                                        // Check if the appointment touches ANY part of this 1-hour cell
                                        return $appStart->lt($cellEndTime) && $appEnd->gt($cellStartTime);
                                    });

                                    if ($meeting) {
                                        // Build the tooltip
                                        $parentName = strtoupper(optional($meeting->parent->student)->first_name . ' ' . optional($meeting->parent->student)->last_name ?: optional($meeting->parent)->username);
                                        $meetingTooltip = $parentName . ' • ' . $meeting->discussion_topic . ' • ' . \Carbon\Carbon::parse($meeting->start_time)->format('g:iA') . ' - ' . \Carbon\Carbon::parse($meeting->end_time)->format('g:iA');
                                        
                                        // 3. Calculate exactly how much of THIS specific cell is covered
                                        $appStart = \Carbon\Carbon::parse($meeting->appointment_date . ' ' . $meeting->start_time);
                                        $appEnd = \Carbon\Carbon::parse($meeting->appointment_date . ' ' . $meeting->end_time);

                                        // Find the exact boundaries of the overlap inside this hour
                                        $overlapStart = $appStart->max($cellStartTime);
                                        $overlapEnd = $appEnd->min($cellEndTime);
                                        
                                        $durationInCell = $overlapStart->diffInMinutes($overlapEnd);
                                        
                                        if ($durationInCell >= 60) {
                                            $cellClass = 'cell-green'; // Covers the whole hour
                                        } elseif ($durationInCell <= 30) {
                                            // Check if the portion inside this cell starts at :00 or :30
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
            <p class="disclaimer">
                Schedules booked on "On Leave" will be pending for reschedule.
            </p>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODALS SECTION -->
<!-- ============================================== -->

<div id="requestsModalOverlay" class="modal-overlay hidden">
    <div class="requests-modal">
        <div class="modal-header">
            <h2><i class="fa-solid fa-user-plus"></i> Incoming Requests</h2>
            <button class="close-btn" onclick="closeModal('requestsModalOverlay')">&times;</button>
        </div>

        <table class="modal-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Topic</th>
                    <th>Date and Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incomingRequests as $request)
                <tr>
                    <td>
                        {{ strtoupper(optional($request->parent->student)->first_name . ' ' . optional($request->parent->student)->last_name ?: optional($request->parent)->username) }}
                    </td>
                    <td>{{ $request->discussion_topic }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($request->appointment_date)->format('M j') }}, 
                        {{ \Carbon\Carbon::parse($request->start_time)->format('g:iA') }} - 
                        {{ \Carbon\Carbon::parse($request->end_time)->format('g:iA') }}
                    </td>
                    <td>
                        <div class="action-buttons">
                            <form action="{{ route('appointments.approve', $request->id) }}" method="POST" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-flat btn-approve">Approve</button>
                            </form>
                            <button type="button" class="btn-flat btn-reschedule" onclick="openRescheduleModal({{ $request->id }})">Reschedule</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No incoming appointment requests.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Nested Reschedule Modal -->
        <div id="rescheduleModal" class="nested-modal hidden">
            <div class="nested-modal-content">
                <h3>State your reason for rescheduling</h3>
                
                <form id="rescheduleForm" method="POST" action="">
                    @csrf @method('PATCH')
                    <input type="text" name="reason" class="reason-input" placeholder="" required>
                    
                    <div class="nested-modal-actions">
                        <button type="button" class="btn-flat btn-decline" onclick="closeModal('rescheduleModal')" style="border: 2px solid #000; padding: 10px 30px;">Cancel</button>
                        <button type="submit" class="btn-flat btn-approve" style="border: 2px solid #000; padding: 10px 30px;">Send</button>
                    </div>
                </form>
            </div>
        </div>
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
        const modal = document.getElementById('rescheduleModal');
        const form = document.getElementById('rescheduleForm');
        
        form.action = `/appointments/${appointmentId}/reschedule`;
        
        openModal('rescheduleModal');
    }

    window.onclick = function(event) {
        const overlay = document.getElementById('requestsModalOverlay');
        if (event.target === overlay) {
            closeModal('requestsModalOverlay');
        }
    }
</script>
@endsection