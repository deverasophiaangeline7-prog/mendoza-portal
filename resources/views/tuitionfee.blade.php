<x-guest-layout>

    <main class="relative flex-grow flex items-center justify-center py-16 bg-cover bg-center w-full" 
          style="background-image: url('{{ asset('images/tuitionfee.jpg') }}');">
        
        <div class="absolute inset-0 bg-black/70"></div>

        <div class="container mx-auto max-w-lg relative z-10 px-4 w-full">
            
            <div class="text-center mb-10">
                <h2 class="text-3xl md:text-4xl font-black text-white uppercase tracking-widest drop-shadow-md">
                    Courses Offered
                </h2>
            </div>

            <div class="flex flex-col gap-6">
                
                <div class="bg-gray-50 rounded-2xl py-8 px-6 shadow-xl flex flex-col items-center text-center border-t-8 border-orange-400 w-full">
                    
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-wide mb-3">
                        Pre-Elementary
                    </h3>
                    
                    <div class="text-base md:text-lg font-bold text-slate-700 whitespace-nowrap">
                        Nursery <span class="text-gray-400 mx-2">|</span> Kindergarten <span class="text-gray-400 mx-2">|</span> Preparatory
                    </div>
                </div>

                <div class="bg-gray-50 rounded-2xl py-8 px-6 shadow-xl flex flex-col items-center text-center border-t-8 border-red-700 w-full">
                    
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-wide mb-3">
                        Elementary
                    </h3>
                    
                    <div class="text-base md:text-lg font-bold text-slate-700">
                        Grades 1 - 6
                    </div>
                </div>

            </div>
        </div>
    </main>

</x-guest-layout>