<x-guest-layout>

    <main class="relative flex-grow flex items-center justify-center py-20 bg-cover bg-center bg-no-repeat w-full" 
          style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset('images/about.jpg') }}');">
        
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-10 max-w-6xl relative z-10 px-4">
            
            <!-- Mission Card (Orange Theme) -->
            <div class="bg-gray-50 rounded-2xl p-8 md:p-10 shadow-2xl flex flex-col items-center text-center border-t-[8px] border-b-[8px] border-orange-400 h-full">                
                <h2 class="text-2xl font-black text-slate-800 uppercase tracking-wide mb-6">
                    Mission
                </h2>
                <div class="leading-relaxed text-slate-700 font-bold text-lg flex-grow flex items-center">
                    Mendoza Academy, Inc. aims to develop the inner potentials of individuals and mold them into young adults who are intellectually and academically competent appreciative of the cultural heritage of their environment, disciplined and determined to contribute their best effort as future leaders to the community with a well-bred conscience to distinguish what is right and wrong and their righteous commitment to the Lord Almighty.
                </div>
            </div>

            <!-- Vision Card (Red Theme) -->
            <div class="bg-gray-50 rounded-2xl p-8 md:p-10 shadow-2xl flex flex-col items-center text-center border-t-[8px] border-b-[8px] border-red-700 h-full">                
                <h2 class="text-2xl font-black text-slate-800 uppercase tracking-wide mb-6">
                    Vision
                </h2>
                <div class="leading-relaxed text-slate-700 font-bold text-lg flex-grow flex items-center">
                    As a private institution, Mendoza Academy, Inc. perceives itself that through competency in imparting discipline, quality education, full understanding of the individual differences among learners and honing them for their total development to be physically, mentally, socially, emotionally and spiritually fit, is conclusive of producing holistic graduates who are God-fearing aware of their worth and dignity.
                </div>
            </div>

        </div>
    </main>

</x-guest-layout>