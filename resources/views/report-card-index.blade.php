<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Report Card Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style> 
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); } 
    </style>
</head>
<body class="bg-gray-100">
    <header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-50">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow" alt="Logo">
            <h1 class="text-2xl font-bold uppercase tracking-tight text-white">Mendoza Academy, Inc.</h1>
        </div>
    </header>

    <div class="flex min-h-screen">
        <nav class="w-64 bg-[#b91c1c] text-white pt-4">
            <ul class="space-y-1">
                <li><a href="{{ route('dashboard') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-chart-line w-6"></i><span>Dashboard</span></a></li>
                <li class="bg-orange-400 mx-2 rounded-lg">
                    <a href="{{ route('reportcard.index') }}" class="flex items-center p-3 space-x-3 text-black font-bold"><i class="fa-solid fa-star w-6"></i><span>Report Card</span></a>
                </li>
            </ul>
        </nav>

        <main class="flex-1 p-8 bg-white">
            <div class="max-w-6xl mx-auto text-center">
                <h2 class="text-6xl font-black text-black uppercase tracking-tighter mb-12 drop-shadow-[4px_4px_0px_rgba(255,255,255,1)]">
                    REPORT CARD
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                    @foreach($sections as $section)
                        <a href="{{ route('reportcard.show', $section->section_id) }}" 
                           class="bg-[#ffb31a] border-[3px] border-black rounded-[40px] py-8 flex flex-col items-center group transition-all shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:translate-y-1 hover:shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            
                            <span class="text-4xl font-black text-black uppercase mb-1" style="-webkit-text-stroke: 1.5px white;">
                                {{ is_numeric($section->grade_level) ? 'GRADE ' . $section->grade_level : $section->grade_level }}
                            </span>

                            <span class="text-xl font-bold text-black uppercase italic tracking-wider">
                                {{ $section->section_name }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </main>
    </div>
</body>
</html>