<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - NKP Report Card</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
        .deped-table th, .deped-table td { border: 1px solid black; padding: 0.4rem 0.6rem; vertical-align: middle; }
        .deped-table th { background-color: #f3f4f6; font-weight: 800; text-transform: uppercase; }
        .form-select-pill { border: 1px solid #999; height: 32px; width: 100%; text-align: center; font-weight: 900; outline: none; cursor: pointer; border-radius: 4px; background: #fff; }
        .category-header { background-color: #e5e7eb; font-weight: 900; font-size: 1.1rem; }
        .sub-category-header { background-color: #f9fafb; font-weight: 800; font-size: 0.95rem; color: #4b5563; }
    </style>
</head>
<body class="bg-gray-100">

<header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-50">
    <div class="flex items-center space-x-3">
        <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow" alt="Logo">
        <h1 class="text-2xl font-bold uppercase tracking-tight">Mendoza Academy, Inc.</h1>
    </div>
    <div class="flex items-center space-x-6 text-2xl">
        <button onclick="window.location.href='{{ route('dashboard') }}'" class="hover:scale-110 transition-transform">
            <i class="fa-solid fa-house"></i>
        </button>
    </div>
</header>

<div class="flex min-h-screen">
    <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0 shadow-2xl z-40">
    <ul class="space-y-1">
        <!-- Dashboard -->
        <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line" :active="request()->routeIs('dashboard')">
            Dashboard
        </x-sidebar-link>

        <!-- Student Information: ONLY for Parents -->
        @if(auth()->user()->role === 'parent')
            <x-sidebar-link href="{{ route('student.view') }}" icon="fa-solid fa-user-graduate" :active="request()->routeIs('student.view')">
                Student Information
            </x-sidebar-link>
        @endif

        <!-- Advisory Class: ONLY for Teachers -->
        @if(auth()->user()->role === 'teacher')
            <x-sidebar-link href="{{ route('students.index') }}" icon="fa-solid fa-chalkboard-user" :active="request()->routeIs('students.*')">
                Advisory Class
            </x-sidebar-link>
        @endif

        <!-- Student Calendar: Role-Based Routing -->
        @php
            $calendarRoute = match(auth()->user()->role) {
                'admin' => route('admin.student.participation'),
                'parent' => route('student.calendar'),
                default => route('student.calendar.index'),
            };
        @endphp
        <x-sidebar-link href="{{ $calendarRoute }}" 
            icon="fa-solid fa-calendar-days" 
            :active="request()->routeIs('admin.student.participation') || request()->routeIs('student.calendar*')">
            Student Calendar
        </x-sidebar-link>

        <!-- Report Card: Role-Based Routing -->
        <x-sidebar-link 
            href="{{ auth()->user()->role === 'parent' ? route('parent.reportcard') : route('reportcard.index') }}" 
            icon="fa-solid fa-star" 
            :active="request()->routeIs('reportcard.*') || request()->routeIs('parent.reportcard')">
            Report Card
        </x-sidebar-link>
        
        <!-- Attendance: Role-Based Routing -->
        <x-sidebar-link 
            href="{{ auth()->user()->role === 'parent' ? route('parent.attendance') : route('attendance.index') }}" 
            icon="fa-solid fa-calendar-check" 
            :active="request()->routeIs('attendance.*') || request()->routeIs('parent.attendance')">
            Attendance
        </x-sidebar-link>

        <!-- Account Management: ONLY for Admin -->
        @if(auth()->user()->role === 'admin')
            <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear" :active="request()->routeIs('account.management')">
                Account Management
            </x-sidebar-link>
        @endif
    </ul>
</nav>

    <main class="flex-1 p-6 bg-white" x-data="nkpData()">
        <div class="max-w-5xl mx-auto">
            
            <div class="flex justify-between items-start mb-6 border-b-4 border-black pb-4">
                <div>
                    <h2 class="text-4xl font-black uppercase text-black">{{ $studentName }}</h2>
                    <h3 class="text-2xl font-bold text-blue-700 uppercase">{{ $sectionName }} (NKP Checklist)</h3>
                </div>
                
                <div class="flex flex-col items-end space-y-3">
                    
                    @if($canManage)
                    <div class="flex space-x-2">
                        <button @click="isManaging = !isManaging" class="font-black px-4 py-2 border-[3px] border-black rounded shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all" :class="isManaging ? 'bg-green-400' : 'bg-gray-200'">
                            <i class="fa-solid" :class="isManaging ? 'fa-unlock' : 'fa-lock'"></i> <span x-text="isManaging ? ' EDITING' : ' VIEWING'"></span>
                        </button>
                        <button x-show="isManaging" x-cloak @click="saveNKP()" class="bg-[#ffaf2e] text-black px-4 py-2 rounded border-[3px] border-black font-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all">
                            <i class="fa-solid fa-floppy-disk mr-1"></i> SAVE ALL
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <p class="text-center italic mb-6 font-bold text-lg">Ang bawat kasanayan ay mamarkahan ng: Beginning (B), Development (D), Consistent (C)</p>

            <div class="space-y-10 mb-20">
                @php
                    $curriculum = [
                        'KALUSUGAN, MAAYOS NA PAKIRAMDAM AT TAMANG PAGKILOS' => [
                            'skills' => [
                                'Naisasagawa ang kasanayang pagkalusugan ng nagpapanatili ng pansariling kalinisan at kaligtasan sa mga sakit',
                                'Naipapakita ang pag-uugali na nagtataguyod sa pansariling kaligtasan',
                                'Naisasagawa ang kasanayang lokomotor tulad ng paglalakad, pagtakbo, pag-eskapo, pagtalon, pag-akyat ng wasto habang naglalaro, pagsasayaw o pag-eehersisyo',
                                'Naisasagawa ang kasanayang di-lokomotor tulad ng pagtulak, paghila, pag-ikot, pag-indayog, pagbaluktot, pagbato, pagsalo at pagsipa nang wasto habang naglalaro',
                                'Naisasagawa ang kasanayang fine motor na kinakailangan para sa pangangalaga sa sarili tulad ng pagsisipilyo, pagbobotones, at paggamit ng kutsara at tinidor',
                                'Naisasagawa ang kasanayang fine motor na kinakailangan para sa mga gawaing sining tulad ng pagpunit, paggupit, pagdikit, pagkopya, pagguhit, pagkulay at iba pa',
                                'Nakababakat, nakakakopya o nakasusulat ng mga titik at bilang'
                            ]
                        ],
                        'PANGKAUNLARANG SOSYO-EMOSYONAL' => [
                            'skills' => [
                                'Nasasabi ang personal na impormasyon (pangalan, kasarian, edad, kapanganakan)',
                                'Naipapahayag ang sariling kagustuhan at pangangailangan',
                                'Naipapakita ang kahandaan na sumubok ng bagong karanasan at tiwala sa sarili sa paggawa nang mag-isa',
                                'Naipapahayag sa positibong paraan ang nararamdaman sa iba\'t-ibang sitwasyon',
                                'Nakasusunod sa mga itinakdang tuntunin sa paaralan ng maluwag sa kalooban at naisasagawa ng maayos ang mga gawain',
                                'Nakikilala ang mga pangunahing emosyon at nagkakarooon ng kamalayan sa damdamin ng iba at naipapakita ang kusang pagtulong',
                                'Naipapakita ang paggalang sa mga kapwa bata at mga nakatatanda',
                                'Natutukoy ang bumubuo sa sariling pamilya',
                                'Nakikilala ang mga tauhan at natutukoy ang mga lugar sa paaralan at komunidad'
                            ]
                        ],
                        'WIKA, PAGKATUTONG BUMASA AT PAKIKIPAGTALASTASAN' => [
                            'sections' => [
                                'PAKIKINIG AT PANONOOD' => [
                                    'Natutukoy ang pagkakaiba ng mga uri ng tunog. Hal. malakas o mahina, mataas o mababa',
                                    'Nakikinig nang mabuti sa kuwento/tula/awit',
                                    'Nasasabi ang mga detalye mula sa napakinggang kuwento/tula/awit',
                                    'Naiuugnay sa sariling karanasan ang mga pangyayari sa kuwento',
                                    'Napagsusunod-sunod ang mga pangyayari sa kuwentong napakinggan',
                                    'Nahihinuha ang mga katangian at mga damdamin ng tauhan sa napakinggang kuwento',
                                    'Natutukoy ang kaugnayan ng mga simpleng sanhi at bunga, problema at solusyon mula sa mga pangyayari sa kuwentong napakinggan',
                                    'Nahihinuha ang maaaring mangyari o magiging wakas ng kuwento',
                                    'Natutukoy ang mga bagay/larawan na magkatulad o magkaiba, mga nawawalang bahagi sa bagay o larawan at mga bagay na hindi kabilang sa grupo'
                                ],
                                'PAGSASALITA' => [
                                    'Nagagamit ang tamang ekspresyon sa pagpapakilala sa sarili at magalang na pagbati ayon sa sitwasyon',
                                    'Nagagamit ang angkop na salita sa paglalarawan ng tao, bagay, atbp.',
                                    'Aktibong nakikiisa sa mga talakayan/gawain sa silid aralan tulad ng tula, tugma atbp. sa pamamagitan ng tamang pagsagot sa mga tanong',
                                    'Nagtatanong ng mga simpleng katanungan gamit ang sino, ano, saan, kailan at bakit',
                                    'Nakakapagbigay ng 1 hanggang 2 direksyon',
                                    'Naikukuwento ang mga simpleng napakinggang kuwento at mga pansariling karanasan'
                                ],
                                'PAGBASA' => [
                                    'Natutukoy ang mga tunog ng mga titik ng alpabeto [a] hanggang [z]',
                                    'Natutukoy ang mga malalaki at maliliit na titik',
                                    'Naiuugnay ang malaking titik sa maliit na titik',
                                    'Natutukoy ang unang tunog ng salitang napakinggan',
                                    'Natutukoy ang mga salitang magkasingtunog/magkatugma',
                                    'Nasasabi ang bilang ng pantig ng salitang napakinggan',
                                    'Natutukoy ang mga bahagi ng isang aklat (pamagat, may akda, gumuhit)',
                                    'Naipapakita ang interes sa pagbasa sa pamamagitan ng pagbuklat ng mga pahina ng libro',
                                    'Nakukuha ang impormasyon mula sa simpleng pictograph, mapa atbp.'
                                ],
                                'PAGSULAT' => [
                                    'Naisusulat ang sariling pangalan',
                                    'Naisusulat ang malaki at maliit na titik ng alpabeto',
                                    'Naipapahayag ang simple/sariling ideya sa pamamagitan ng mga simbolo'
                                ]
                            ]
                        ],
                        'MATEMATIKA' => [
                            'skills' => [
                                'Natutukoy ang mga kulay at hugis',
                                'Napagsama-sama ang mga bagay ayon sa hugis, laki at kulay',
                                'Pinaghahambing / isinasaayos ang mga bagay ayon sa katangian',
                                'Natutukoy ang pattern at naitutuloy ito',
                                'Nasasabi ang oras gamit ang analog clock',
                                'Nasasabi ang ngalan ng mga araw sa isang linggo at buwan sa isang taon',
                                'Nakabibilang nang lalagpas sa 20',
                                'Napagsusunod-sunod ang mga bilang',
                                'Natutukoy ang ordinal na bilang ng mga bagay',
                                'Nakasasagot ng simpleng addition at subtraction problems',
                                'Naipapangkat ang mga bagay na may katulad na bilang hanggang 10',
                                'Nasusukat ang haba, laki at bigat ng mga bagay gamit ang non-standard na panukat',
                                'Nakikilala ang halaga ng pera hanggang P20'
                            ]
                        ],
                        'PAG-UNAWA SA PISIKAL AT NATURAL NA KAPALIGIRAN' => [
                            'skills' => [
                                'Natutukoy ang mga bahaging katawan at ang mga gawain nito',
                                'Nakapagtatala ng mga namasid at nakita sa mga datos gamit ang larawan, bilang o simbolo',
                                'Natutukoy ang mga bahagi ng halaman / hayop at ang gawain nito',
                                'Napapangkat ang mga hayop sa iba-ibang katangian',
                                'Nasasabi ang pangunahing pangangailangan ng mga halaman / hayop / kapaligiran',
                                'Natutukoy ang iba\'t ibang uri ng panahon'
                            ]
                        ]
                    ];
                @endphp

                @foreach($curriculum as $catName => $content)
            <div class="border-black rounded-2xl bg-gray-50 mb-20 shadow-sm">                    
                <table class="w-full text-sm deped-table bg-white">
                        <thead>
                            <tr class="category-header">
                                <th class="text-left w-2/3 uppercase">{{ $catName }}</th>
                                <th class="w-12">Q1</th><th class="w-12">Q2</th><th class="w-12">Q3</th><th class="w-12">Q4</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($content['skills']))
                                @foreach($content['skills'] as $skill)
                                <tr>
                                    <td class="font-medium">{{ $skill }}</td>
                                    @for($q=1; $q<=4; $q++)
                                    <td class="p-0">
                                        <select x-show="isManaging" x-model="evaluations['{{ $skill }}'].q{{ $q }}" class="form-select-pill">
                                            <option value=""></option><option value="B">B</option><option value="D">D</option><option value="C">C</option>
                                        </select>
                                        <span x-show="!isManaging" class="block text-center font-black text-blue-600 text-lg" x-text="evaluations['{{ $skill }}'].q{{ $q }}"></span>
                                    </td>
                                    @endfor
                                </tr>
                                @endforeach
                            @elseif(isset($content['sections']))
                                @foreach($content['sections'] as $subName => $subSkills)
                                <tr class="sub-category-header"><td colspan="5" class="italic pl-4">{{ $subName }}</td></tr>
                                @foreach($subSkills as $skill)
                                <tr>
                                    <td class="font-medium pl-8">{{ $skill }}</td>
                                    @for($q=1; $q<=4; $q++)
                                    <td class="p-0">
                                        <select x-show="isManaging" x-model="evaluations['{{ $skill }}'].q{{ $q }}" class="form-select-pill">
                                            <option value=""></option><option value="B">B</option><option value="D">D</option><option value="C">C</option>
                                        </select>
                                        <span x-show="!isManaging" class="block text-center font-black text-blue-600 text-lg" x-text="evaluations['{{ $skill }}'].q{{ $q }}"></span>
                                    </td>
                                    @endfor
                                </tr>
                                @endforeach
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>

            <div class="mt-12 p-8 border border-black rounded-2xl bg-gray-50 mb-20 shadow-sm">
                <h5 class="font-black text-center text-2xl mb-6 uppercase tracking-tighter">Iskala ng Pagmamarka</h5>
                <table class="w-full text-sm deped-table bg-white">
                    <thead>
                        <tr class="bg-black text-black"><th>MARKA</th><th>BATAYAN</th></tr>
                    </thead>
                    <tbody>
                        <tr><td class="font-black text-lg">Beginning (B)</td><td class="italic">Bihirang naipapakita ang kasanayan / Nangangailangan ng lubos na paggabay</td></tr>
                        <tr><td class="font-black text-lg">Development (D)</td><td class="italic">Minsan naipapakita ang kasanayan / Patuloy na umuunlad</td></tr>
                        <tr><td class="font-black text-lg">Consistent (C)</td><td class="italic">Laging naisasagawa ang kasanayan at may higit pang pagsulong</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="showToast" x-cloak class="fixed bottom-10 right-10 z-50 px-10 py-5 rounded-2xl border-[4px] border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] bg-green-400 text-black font-black uppercase text-2xl">
            <i class="fa-solid fa-circle-check mr-2"></i> SAVED SUCCESSFULLY!
        </div>
    </main>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('nkpData', () => ({
            isManaging: false,
            showToast: false,
            studentId: '{{ $student_id }}',
            evaluations: @json($savedEvaluations) || {},

            init() {
                if (Array.isArray(this.evaluations)) this.evaluations = {};
                
                // Initialize all skill keys automatically from the DOM to avoid manual typing in JS
                document.querySelectorAll('tbody tr:not(.sub-category-header)').forEach(row => {
                    let skillText = row.querySelector('td:first-child').innerText.trim();
                    if(!this.evaluations[skillText]) {
                        this.evaluations[skillText] = { q1: '', q2: '', q3: '', q4: '' };
                    }
                });
            },

            saveNKP() {
                fetch('{{ route('reportcard.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ 
                        student_id: this.studentId, 
                        nkp_evaluations: this.evaluations 
                    })
                })
                .then(res => res.json())
                .then(() => {
                    this.showToast = true;
                    setTimeout(() => this.showToast = false, 3000);
                })
                .catch(err => alert('Error saving data.'));
            }
        }));
    });
</script>
</body>
</html>