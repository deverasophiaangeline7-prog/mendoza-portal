<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Student List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style> .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); } </style>
</head>
<body class="bg-gray-100">
    <header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-50">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow">
            <h1 class="text-2xl font-bold uppercase tracking-tight text-white">Mendoza Academy, Inc.</h1>
        </div>
    </header>

    <div class="flex min-h-screen">
        <nav class="w-64 bg-[#b91c1c] text-white pt-4">
            <ul class="space-y-1">
                <li><a href="{{ route('dashboard') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-chart-line w-6"></i><span>Dashboard</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-user-graduate w-6"></i><span>List of Students</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-days w-6"></i><span>Student Calendar</span></a></li>
                <li class="bg-orange-400 mx-2 rounded-lg">
                    <a href="{{ route('reportcard.index') }}" class="flex items-center p-3 space-x-3 text-black font-bold"><i class="fa-solid fa-star w-6"></i><span>Report Card</span></a>
                </li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-wallet w-6"></i><span>Tuition Fee</span></a></li>
                <li><a href="{{ route('attendance.index') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-check w-6"></i><span>Attendance</span></a></li>
            </ul>
        </nav>

        <main class="flex-1 p-8 bg-white">
            <div class="max-w-6xl mx-auto">
                <div class="flex justify-between items-center mb-8 border-b-4 border-black pb-4">
                    <div>
                        <h2 class="text-4xl font-black text-black uppercase tracking-tight">Student List</h2>
                        <h3 class="text-2xl font-bold text-orange-500 uppercase">{{ $sectionName }}</h3>
                    </div>
                    <a href="{{ route('reportcard.index') }}" class="text-red-600 text-5xl hover:scale-110 transition leading-none">
                        <i class="fa-solid fa-circle-left"></i>
                    </a>
                </div>

                <div class="border-[3px] border-black rounded-xl overflow-hidden shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                    <table class="w-full text-left border-collapse bg-white">
                        <thead>
                            <tr class="bg-gray-100 border-b-[3px] border-black text-black">
                                <th class="p-4 border-r-[3px] border-black w-24 text-center font-black text-2xl">NO.</th>
                                <th class="p-4 uppercase font-black text-2xl">Learner's Name</th>
                                <th class="w-48"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['Male', 'Female'] as $gender)
                                <tr class="bg-gray-200 border-b-[2px] border-black text-black">
                                    <td class="p-3 px-6 font-black text-xl border-r-[3px] border-black italic" colspan="3">{{ strtoupper($gender) }}</td>
                                </tr>
                                @php $count = 1; @endphp
                                @foreach($students->where('gender', $gender) as $student)
                                <tr class="border-b-[2px] border-black last:border-b-0 hover:bg-yellow-50 transition-colors text-black">
                                    <td class="p-4 text-center font-bold text-xl border-r-[3px] border-black text-gray-400">{{ $count++ }}</td>
                                    <td class="p-4 px-6 font-black text-2xl uppercase">{{ $student->last_name }}, {{ $student->first_name }}</td>
                                    <td class="p-4 text-center">
                                        <a href="{{ route('reportcard.showStudent', $student->student_id) }}" 
                                           class="bg-[#ffaf2e] hover:bg-orange-500 text-black px-8 py-1.5 rounded-xl font-black border-[2px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-all inline-block">
                                            VIEW
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>