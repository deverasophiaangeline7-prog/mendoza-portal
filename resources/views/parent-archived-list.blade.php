<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Archived Parents</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-gray-100" x-data="{ restoreModal: false, restoreUrl: '' }">

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
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0 shadow-2xl z-40">
    <ul class="space-y-1">
        <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line" :active="request()->routeIs('dashboard')">
            Dashboard
        </x-sidebar-link>
        @if(auth()->user()->role === 'admin')
            <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="request()->routeIs('account.management')">
                Account Management
            </x-sidebar-link>
        @endif
    </ul>
</nav>

        <main class="flex-1 p-8">
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h2 class="text-4xl font-black text-black uppercase tracking-tight">List of Accounts</h2>
                    <h3 class="text-2xl font-bold text-gray-500 mt-1 italic">Archived Parents/Students</h3>
                </div>
                
                <div class="flex gap-4">
                    <a href="{{ route('parent.list') }}" class="bg-gray-800 hover:bg-black text-white px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        <i class="fa-solid fa-arrow-left"></i> Back to Grades
                    </a>
                </div>
            </div>

            <div class="border-2 border-black rounded-lg overflow-hidden bg-white shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-200 border-b-2 border-black text-xl font-bold">
                        <tr>
                            <th class="p-4 border-r-2 border-black text-center w-24">No.</th>
                            <th class="p-4 border-r-2 border-black w-40">LRN</th>
                            <th class="p-4 border-r-2 border-black">Learner</th>
                            <th class="p-4">Previous Grade/Section</th>
                            <th class="p-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archivedStudents as $index => $student)
                            <tr class="border-b-2 border-black hover:bg-gray-50 transition text-gray-500">
                                <td class="p-4 border-r-2 border-black text-center font-bold">{{ $index + 1 }}</td>
                                <td class="p-4 border-r-2 border-black font-bold">{{ $student->lrn }}</td>
                                <td class="p-4 border-r-2 border-black font-bold uppercase">{{ $student->first_name }} {{ $student->last_name }}</td>
                                <td class="p-4 font-bold">{{ $student->grade_level }} - {{ $student->section->section_name ?? 'N/A' }}</td>
                                <td class="p-4">
                                    <div class="flex justify-center gap-2 items-center">
                                        <!-- RESTORE BUTTON -->
                                        <button type="button" 
                                            @click="restoreModal = true; restoreUrl = '{{ route('account.parent.restore', $student->user_id) }}'" 
                                            title="Restore Account" 
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-full font-bold text-sm transition-colors flex items-center gap-2">
                                            <i class="fa-solid fa-arrow-rotate-left"></i> Restore
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500 font-bold text-xl">No archived parents found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Restore Confirmation Modal -->
    <div x-show="restoreModal" 
         x-transition:opacity
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
         x-cloak>
        <div class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]" 
             @click.away="restoreModal = false">
            <div class="text-center">
                <i class="fa-solid fa-arrow-rotate-left text-6xl text-blue-500 mb-6"></i>
                <h2 class="text-3xl font-black mb-4 uppercase">Restore Account?</h2>
                <p class="text-lg font-medium text-gray-600 mb-8 leading-tight">
                    Are you sure you want to restore this parent account? The student will reappear on the active list.
                </p>
                <div class="flex flex-col gap-4">
                    <form :action="restoreUrl" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full bg-blue-500 text-white font-black py-4 rounded-full border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-600 active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all">
                            YES, RESTORE
                        </button>
                    </form>
                    <button @click="restoreModal = false" type="button" class="w-full bg-gray-100 text-gray-700 font-black py-4 rounded-full border-2 border-black hover:bg-gray-200 transition-all">
                        CANCEL
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>