@extends('layouts.navigation')

@section('title', 'NKP Report Card')

@section('content')
<style>
    .deped-table th, .deped-table td { border: 1px solid black; padding: 0.4rem 0.6rem; vertical-align: middle; }
    .deped-table th { background-color: #f3f4f6; font-weight: 800; text-transform: uppercase; }
    .form-select-pill { border: 1px solid #999; height: 32px; width: 100%; text-align: center; font-weight: 900; outline: none; cursor: pointer; border-radius: 4px; background: #fff; }
    .category-header { background-color: #e5e7eb; font-weight: 900; font-size: 1.1rem; }
    .sub-category-header { background-color: #f9fafb; font-weight: 800; font-size: 0.95rem; color: #4b5563; }
</style>

<div class="flex-1 p-6 bg-white min-h-screen relative" x-data="nkpData()">
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
                    <tr class="bg-black text-white border-black"><th>MARKA</th><th>BATAYAN</th></tr>
                </thead>
                <tbody>
                    <tr><td class="font-black text-lg">Beginning (B)</td><td class="italic">Bihirang naipapakita ang kasanayan / Nangangailangan ng lubos na paggabay</td></tr>
                    <tr><td class="font-black text-lg">Development (D)</td><td class="italic">Minsan naipapakita ang kasanayan / Patuloy na umuunlad</td></tr>
                    <tr><td class="font-black text-lg">Consistent (C)</td><td class="italic">Laging naisasagawa ang kasanayan at may higit pang pagsulong</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Success Toast -->
    <div x-show="showToast" x-cloak class="fixed bottom-10 right-10 z-50 px-10 py-5 rounded-2xl border-[4px] border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] bg-green-400 text-black font-black uppercase text-2xl">
        <i class="fa-solid fa-circle-check mr-2"></i> SAVED SUCCESSFULLY!
    </div>

    <!-- Change Password Modal -->
    @if(auth()->user()->role === 'teacher')
    <div x-show="passwordModal" 
         x-transition:opacity
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
         x-cloak>
        
        <div @click.away="passwordModal = false" 
             class="bg-white border-[4px] border-black rounded-[2.5rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative">
            
            <button @click="passwordModal = false" class="absolute top-6 right-8 text-4xl font-black text-gray-400 hover:text-black transition-colors">&times;</button>

            <h2 class="text-3xl font-black italic uppercase tracking-tight mb-8">Change Password</h2>

            <form action="{{ route('user.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-5">
                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">Current Password</label>
                        <input type="password" name="current_password" required 
                               class="w-full border-[3px] rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-all @error('current_password') border-red-500 bg-red-50 focus:ring-red-400 @else border-black focus:ring-green-400 bg-white @enderror">
                        @error('current_password')
                            <p class="text-red-500 font-bold text-sm mt-2 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">New Password</label>
                        <input type="password" name="password" required 
                               class="w-full border-[3px] border-black rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 transition-all bg-white">
                    </div>

                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required 
                               class="w-full border-[3px] border-black rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 transition-all bg-white">
                    </div>
                </div>

                <div class="flex justify-end items-center gap-8 mt-10">
                    <button type="button" @click="passwordModal = false" class="text-black font-black uppercase tracking-widest hover:text-gray-600 transition-colors">
                        Cancel
                    </button>
                    
                    <button type="submit" class="bg-[#22C55E] text-white font-black py-3 px-8 rounded-2xl border-[3px] border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] hover:brightness-95 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> UPDATE
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

<!-- NKP Alpine.js Logic -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('nkpData', () => ({
            isManaging: false,
            showToast: false,
            // Automatically open the password modal if there is a validation error
            passwordModal: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }},
            studentId: '{{ $student_id }}',
            evaluations: @json($savedEvaluations) || {},

            init() {
                // Failsafe check
                if (Array.isArray(this.evaluations)) this.evaluations = {};
                
                // Initialize all skill keys automatically from the DOM to avoid manual typing in JS
                document.querySelectorAll('tbody tr:not(.sub-category-header)').forEach(row => {
                    let firstTd = row.querySelector('td:first-child');
                    if(firstTd) {
                        let skillText = firstTd.innerText.trim();
                        if(!this.evaluations[skillText]) {
                            this.evaluations[skillText] = { q1: '', q2: '', q3: '', q4: '' };
                        }
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
@endsection