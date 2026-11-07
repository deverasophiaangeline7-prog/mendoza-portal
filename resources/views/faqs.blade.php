@php
    $faqQ1 = \App\Models\SiteContent::where('page', 'faqs')->where('key', 'q1')->value('value') ?? 'What grade levels do you offer?';
    $faqA1 = \App\Models\SiteContent::where('page', 'faqs')->where('key', 'a1')->value('value') ?? 'Pre-elementary (Nursery, Kinder, and Preparatory) to Elementary (Grades 1-6)';

    $faqQ2 = \App\Models\SiteContent::where('page', 'faqs')->where('key', 'q2')->value('value') ?? 'Where is the school located?';
    $faqA2 = \App\Models\SiteContent::where('page', 'faqs')->where('key', 'a2')->value('value') ?? 'Blk. 9 Lot 88 Mapagkawanggawa St. Purok 4, Lupang Arenda, Sta. Ana, Taytay, Rizal';

    $faqQ3 = \App\Models\SiteContent::where('page', 'faqs')->where('key', 'q3')->value('value') ?? 'What are your office hours?';
    $faqA3 = \App\Models\SiteContent::where('page', 'faqs')->where('key', 'a3')->value('value') ?? 'Monday to Friday (8:00AM - 5:00PM)';

    $faqQ4 = \App\Models\SiteContent::where('page', 'faqs')->where('key', 'q4')->value('value') ?? 'What are the admission requirements?';
@endphp

<x-guest-layout>

    <main class="relative flex-grow flex items-center justify-center py-12 bg-cover bg-center w-full" 
          style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset('images/faqs.jpg') }}');">
        
        <div class="absolute inset-0 bg-black/20"></div>

        <div class="container mx-auto max-w-5xl relative z-10 px-4">
            <div class="bg-white/80 rounded-3xl p-8 md:p-16 shadow-2xl border border-white/40">
                
                <h2 class="text-3xl md:text-4xl font-black text-black text-center mb-12 uppercase tracking-wide">
                    Frequently Asked Questions:
                </h2>

                <div class="space-y-12">
                    <div class="text-left">
                        <h3 class="text-2xl font-bold text-red-700">1. {{ $faqQ1 }}</h3>
                        <p class="text-xl font-semibold text-black mt-2">{{ $faqA1 }}</p>
                    </div>

                    <div class="text-left">
                        <h3 class="text-2xl font-bold text-red-700">2. {{ $faqQ2 }}</h3>
                        <p class="text-xl font-semibold text-black mt-2">
                            {{ $faqA2 }}
                        </p>
                    </div>

                    <div class="text-left">
                        <h3 class="text-2xl font-bold text-red-700">3. {{ $faqQ3 }}</h3>
                        <p class="text-xl font-semibold text-black mt-2">{{ $faqA3 }}</p>
                    </div>

                    <div class="mt-12">
                        <h3 class="text-2xl font-bold text-red-700 mb-8 text-left uppercase tracking-tight">4. {{ $faqQ4 }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left max-w-4xl mx-auto px-2">
                            
                            <div class="space-y-6">
                                <div class="bg-[#b26905] rounded-3xl p-6 shadow-md border-2 border-black">
                                    <h4 class="font-black text-white text-lg mb-3 uppercase">For incoming Kindergarten:</h4>
                                    <div class="flex items-start gap-3 text-white font-bold">
                                        <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center left-center mt-1 text-xs font-black">✔</span>
                                        <p class="text-sm text-left">Must be five (5) years old on or before August 31, 2026. (Reference: DepEd Order No. 47 s. 2016/ DepEd Order No. 20, s. 2018)</p>
                                    </div>
                                </div>

                                <div class="bg-[#b26905] rounded-3xl p-6 shadow-md border-2 border-black">
                                    <h4 class="font-black text-white text-lg mb-3 uppercase">Document to be submitted:</h4>
                                    <div class="flex items-start gap-3 text-white font-bold">
                                        <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center left-center mt-1 text-xs font-black">✔</span>
                                        <p class="text-sm text-left">Photocopy of Birth Certificate [In the absence of PSA/NSO Birth Certificate, submit a photocopy of Baptismal or Barangay Certificate.]</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="bg-[#b26905] rounded-3xl p-6 shadow-md border-2 border-black">
                                    <h4 class="font-black text-white text-lg mb-3 uppercase">For incoming Grade 1:</h4>
                                    <div class="flex items-start gap-3 text-white font-bold">
                                        <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center left-center mt-1 text-xs font-black">✔</span>
                                        <p class="text-sm text-left">Must be Kindergarten Completer</p>
                                    </div>
                                </div>

                                <div class="bg-[#b26905] rounded-3xl p-6 shadow-md border-2 border-black">
                                    <h4 class="font-black text-white text-lg mb-3 uppercase">For more information:</h4>
                                    <div class="space-y-4 text-white font-bold text-sm text-left">
                                        <div class="flex items-start gap-3">
                                            <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center left-center text-xs font-black">✔</span>
                                            <span>Visit our school or send a message to our official FB Page.</span>
                                        </div>
                                        <div class="flex items-start gap-3">
                                            <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center left-center text-xs font-black">✔</span>
                                            <span>Contact the following persons:</span>
                                        </div>
                                        <div class="pl-8 space-y-3 border-l-2 border-white/30 ml-2">
                                            <div>
                                                <p>Jocelyn A. Mendoza - 09452415916</p>
                                                <p class="text-xs font-normal opacity-90 italic uppercase">Guidance Coordinator</p>
                                            </div>
                                            <div>
                                                <p>Shanel Faith A. Mendoza - 09271647081</p>
                                                <p class="text-xs font-normal opacity-90 italic uppercase">School Coordinator</p>
                                            </div>
                                            <div>
                                                <p>Sonny T. Mendoza - 09150310777</p>
                                                <p class="text-xs font-normal opacity-90 italic uppercase">School Director</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</x-guest-layout>