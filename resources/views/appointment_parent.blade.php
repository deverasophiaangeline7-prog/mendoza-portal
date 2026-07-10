@extends('layouts.navigation')

@section('title', 'Appointment Scheduling (Parent)')

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

    /* Main Content */
    .main-content {
        flex: 1;
        padding: 20px 30px;
        display: flex;
        gap: 30px;
        background-color: #ffffff;
    }

    .left-column { flex: 1; display: flex; flex-direction: column; gap: 20px; }
    .right-column { flex: 1.2; }

    /* ----------------------------------
       LEFT COLUMN STYLES (CANVA MATCH)
       ---------------------------------- */

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

    /* Form Inputs */
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
        border-radius: 25px; /* Pill shape */
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

    /* Request Status Box */
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
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 900;
        font-size: 14px;
    }

    /* Incoming Request Data Row */
    .incoming-data-row {
        display: flex;
        gap: 5px; /* Gap between grey boxes */
        margin-bottom: 15px;
    }

    .incoming-data-row div {
        flex: 1;
        background-color: var(--ma-bg-grey);
        padding: 10px;
        text-align: center;
        font-weight: bold;
        font-size: 14px;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
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

    /* ----------------------------------
       RIGHT COLUMN STYLES (CALENDAR)
       ---------------------------------- */
    .calendar-title {
        text-align: center;
        font-size: 24px;
        font-weight: 900;
        margin: 0 0 5px 0;
    }

    .calendar-navigation {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
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
</style>

<div class="dashboard-container">
    <div class="main-content">
        
        <!-- Left Side: Forms and Statuses -->
        <div class="left-column">
            
            <!-- 1. Appoint with Adviser Form -->
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

            <!-- 2. Request Status Tracker -->
            <div class="status-box-container">
                <div class="status-label-block">
                    Request Status:
                </div>
                <div class="status-data-block">
                    <span>Pending</span>
                    <span class="pill-orange">Reschedule</span>
                </div>
            </div>

            <!-- 3. Incoming Appointment Request -->
            <div class="ma-card">
                <h3>Appointment Request</h3>
                
                <div class="incoming-data-row">
                    <div>Behavior Concern</div>
                    <div>May 12, 10:00AM</div>
                </div>

                <div class="action-buttons">
                    <form action="#" method="POST" style="display:inline;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-flat btn-approve">Approve</button>
                    </form>
                    
                    <form action="#" method="POST" style="display:inline;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-flat btn-decline">Decline</button>
                    </form>

                    <button type="button" class="btn-flat btn-reschedule">Reschedule</button>
                </div>
            </div>

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

            <h2 class="calendar-title">{{ $adviserName ? 'Adviser ' . strtoupper($adviserName) . ' Schedule' : 'Adviser Schedule' }}</h2>
            
            <!-- Dynamic Calendar Navigation -->
            <div class="calendar-navigation">
                <a href="{{ request()->url() }}?date={{ $prevWeekDate }}" class="nav-arrow">&laquo;</a>
                <h2 class="month-title">{{ $currentDate->format('F Y') }}</h2>
                <a href="{{ request()->url() }}?date={{ $nextWeekDate }}" class="nav-arrow">&raquo;</a>
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
                                        if ($slot->status === 'booked') $cellClass = 'cell-green';
                                        elseif ($slot->status === 'class') $cellClass = 'cell-red';
                                        elseif ($slot->status === 'leave') $cellClass = 'cell-grey';
                                    }
                                @endphp
                                <td class="{{ $cellClass }}"></td>
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
@endsection