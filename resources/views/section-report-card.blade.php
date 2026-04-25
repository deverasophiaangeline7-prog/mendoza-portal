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
        <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow" alt="Logo">
        <h1 class="text-2xl font-bold uppercase tracking-tight">Mendoza Academy, Inc.</h1>
    </div>
    
    <div class="flex items-center space-x-6 text-2xl">
        <x-top-icon-button>
            <i class="fa-solid fa-envelope relative">
                <span class="absolute -top-2 -right-2 bg-yellow-400 text-red-700 text-xs rounded-full h-5 w-5 flex items-center justify-center border border-red-700 font-bold">1</span>
            </i>
        </x-top-icon-button>
        
        <x-top-icon-button>
            <i class="fa-solid fa-bell"></i>
        </x-top-icon-button>
        
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" class="hover:scale-110 transition-transform focus:outline-none flex items-center">
                <i class="fa-solid fa-circle-user text-orange-400 text-4xl"></i>
            </button>

            <div x-show="open" 
                 x-transition 
                 class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-2xl py-1 z-50 border border-gray-200 overflow-hidden"
                 style="display: none;"
                 x-cloak>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors font-bold">
                        <i class="fa-solid fa-right-from-bracket mr-3"></i>
                        Logout
                    </button>
                </form>

                <hr class="border-gray-100">

                <button @click="open = false" class="flex w-full items-center px-4 py-3 text-sm text-gray-500 hover:bg-gray-50 transition-colors">
                    <i class="fa-solid fa-xmark mr-3"></i>
                    Cancel
                </button>
            </div>
        </div>
    </div>
</header>

    <div class="flex min-h-screen">
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0">
            <ul class="space-y-1">
                <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-sidebar-link>
                
                <x-sidebar-link href="#" icon="fa-solid fa-user-graduate">
                    List of Students
                </x-sidebar-link>
                
                <x-sidebar-link href="#" icon="fa-solid fa-calendar-days">
                    Student Calendar
                </x-sidebar-link>
                
                <x-sidebar-link href="{{ route('reportcard.index') }}" icon="fa-solid fa-star" :active="request()->routeIs('reportcard.*')">
                    Report Card
                </x-sidebar-link>
                
                <x-sidebar-link href="{{ route('attendance.index') }}" icon="fa-solid fa-calendar-check" :active="request()->routeIs('attendance.*')">
                    Attendance
                </x-sidebar-link>
                
                {{-- Automatically hidden from teachers/parents on the backend --}}
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="request()->routeIs('account.*') || request()->routeIs('teacher.*') || request()->routeIs('parent.*')">
                        Account Management
                    </x-sidebar-link>
                @endif
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