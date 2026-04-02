<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy, Inc.</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('images/MAILogo.png') }}">
    <style>
        .hero-gradient {
            background: linear-gradient(to right, #d32f2f, #8b0000);
        }
        .nav-active {
            background-color: #ffb74d;
            color: #fff;
            border-radius: 9999px;
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <header class="hero-gradient text-white py-4 px-6 shadow-lg">
        <div class="container mx-auto flex flex-wrap justify-between items-center">
            
            <div class="flex items-center space-x-3">
                <div class="p-1 rounded shadow-sm">
                    <img src="{{ asset('images/MAILogo.png') }}" alt="Logo" class="h-10 w-10">
                </div>
                <h1 class="text-2xl font-bold tracking-tight uppercase">Mendoza Academy, Inc.</h1>
            </div>

            <nav class="hidden md:flex items-center space-x-8 font-medium">
    <a href="{{ url('/') }}" class="hover:text-red-200 transition" >Home</a>
    <a href="{{ url('about') }}" class="hover:text-red-200 transition">About</a>
    <a href="{{ url('tuitionfee') }}" class="hover:text-red-200 transition">Tuition Fees</a>
    <a href="{{ url('faqs') }}" class="nav-active px-6 py-2">FAQs</a>
    <a href="{{ route('login') }}" class="bg-orange-400 text-red-900 px-6 py-2 rounded-full font-black shadow-md hover:bg-orange-300 transition">SMS / LOG IN</a>
    </nav>
        </div>
    </header>

   <main class="relative flex-grow flex items-center justify-center py-12 bg-cover bg-center" 
          style="background-image: url('{{ asset('images/faqs.jpg') }}');">
        
        <div class="absolute inset-0 bg-black/20"></div>

        <div class="container mx-auto max-w-5xl relative z-10 px-4">
            <div class="bg-white/80 rounded-3xl p-8 md:p-16 shadow-2xl border border-white/40">
                
                <h2 class="text-3xl md:text-4xl font-black text-black text-center mb-12 uppercase tracking-wide">
                    Frequently Asked Questions:
                </h2>

                <div class="space-y-12">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-red-700">1. What grade levels do you offer?</h3>
                        <p class="text-xl font-semibold text-black mt-2">Pre-elementary (Kinder and Preparatory) to Elementary (Grades 1-6)</p>
                    </div>

                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-red-700">2. Where is the school located?</h3>
                        <p class="text-xl font-semibold text-black mt-2">
                            Blk. 9 Lot 88 Mapagkawanggawa St. Purok 4, Lupang Arenda, Sta. Ana, Taytay, Rizal
                        </p>
                    </div>

                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-red-700">3. What are your office hours?</h3>
                        <p class="text-xl font-semibold text-black mt-2">Monday to Friday (8:00AM - 5:00PM)</p>
                    </div>

                    <div class="mt-12">
                        <h3 class="text-2xl font-bold text-red-700 mb-8 text-center uppercase tracking-tight">4. What are the admission requirements?</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left max-w-4xl mx-auto px-2">
                            
                            <div class="space-y-6">
                                <div class="bg-orange-400 rounded-3xl p-6 shadow-md border-2 border-black">
                                    <h4 class="font-black text-white text-lg mb-3 uppercase">For incoming Kindergarten:</h4>
                                    <div class="flex items-start gap-3 text-white font-bold">
                                        <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center justify-center mt-1 text-xs font-black">✔</span>
                                        <p class="text-sm">Must be five (5) years old on or before August 31, 2026. (Reference: DepEd Order No. 47 s. 2016/ DepEd Order No. 20, s. 2018)</p>
                                    </div>
                                </div>

                                <div class="bg-orange-400 rounded-3xl p-6 shadow-md border-2 border-black">
                                    <h4 class="font-black text-white text-lg mb-3 uppercase">Document to be submitted:</h4>
                                    <div class="flex items-start gap-3 text-white font-bold">
                                        <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center justify-center mt-1 text-xs font-black">✔</span>
                                        <p class="text-sm">Photocopy of Birth Certificate [In the absence of PSA/NSO Birth Certificate, submit a photocopy of Baptismal or Barangay Certificate.]</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="bg-orange-400 rounded-3xl p-6 shadow-md border-2 border-black">
                                    <h4 class="font-black text-white text-lg mb-3 uppercase">For incoming Grade 1:</h4>
                                    <div class="flex items-start gap-3 text-white font-bold">
                                        <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center justify-center mt-1 text-xs font-black">✔</span>
                                        <p class="text-sm">Must be Kindergarten Completer</p>
                                    </div>
                                </div>

                                <div class="bg-orange-400 rounded-3xl p-6 shadow-md border-2 border-black">
                                    <h4 class="font-black text-white text-lg mb-3 uppercase">For more information:</h4>
                                    <div class="space-y-4 text-white font-bold text-sm">
                                        <div class="flex items-start gap-3">
                                            <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center justify-center text-xs font-black">✔</span>
                                            <span>Visit our school or send a message to our official FB Page.</span>
                                        </div>
                                        <div class="flex items-start gap-3">
                                            <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center justify-center text-xs font-black">✔</span>
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
                            </div> </div> </div> </div> </div> </div> </main>

    <footer class="bg-white py-4 border-t border-gray-200">
        <div class="container mx-auto flex flex-wrap justify-center gap-8 text-red-800 font-bold">
            <div class="flex items-center space-x-2">
                <i class="fab fa-facebook text-blue-600 text-2xl"></i>
                <span>Mendoza Academy Inc</span>
            </div>
            <div class="flex items-center space-x-2">
                <i class="fas fa-phone-alt text-red-600"></i>
                <span>09452415916</span>
            </div>
            <div class="flex items-center space-x-2">
                <i class="fas fa-phone-alt text-red-600"></i>
                <span>09081482052</span>
            </div>
        </div>
    </footer>

</body>
</html>