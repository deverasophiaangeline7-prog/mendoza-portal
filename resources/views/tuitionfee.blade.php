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
        .dots {
            flex-grow: 1;
            border-bottom: 3px dotted #000;
            margin: 0 10px;
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
    <a href="{{ url('tuitionfee') }}" class="nav-active px-6 py-2">Tuition Fees</a>
    <a href="{{ url('faqs') }}" class="hover:text-red-200 transition">FAQs</a>
    <a href="{{ route('login') }}" class="bg-orange-400 text-red-900 px-6 py-2 rounded-full font-black shadow-md hover:bg-orange-300 transition">SMS / LOG IN</a>
    </nav>
        </div>
    </header>

    <main class="relative flex-grow flex items-center justify-center py-12 bg-cover bg-center" 
          style="background-image: url('{{ asset('images/tuitionfee.jpg') }}');">
        
        <div class="absolute inset-0 bg-black/20"></div>

        <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl relative z-10 px-4">
            
            <div class="bg-white/80 rounded-3xl p-8 shadow-xl flex flex-col">
                <h2 class="text-red-700 text-2xl font-black text-center mb-8 italic uppercase tracking-wider">PLAN 1 - INSTALLMENT</h2>
                
                <div class="space-y-6 text-xl font-bold text-gray-900">
                    <div class="flex items-center"><span>Registration</span> <div class="dots"></div> <span>₱900</span></div>
                    <div class="flex items-center"><span>Miscellaneous</span> <div class="dots"></div> <span>₱2,600</span></div>
                    <div class="flex items-center border-b-4 border-black pb-2"><span>July</span> <div class="dots"></div> <span>₱1,000</span></div>
                </div>

                <div class="mt-6 self-end">
                    <div class="bg-red-700 text-white text-3xl font-black px-6 py-2 rounded-md shadow-lg italic">
                        = ₱4,500
                    </div>
                </div>
            </div>

            <div class="bg-white/80 rounded-3xl p-8 shadow-xl flex flex-col">
                <h2 class="text-red-700 text-2xl font-black text-center mb-8 italic uppercase tracking-wider">PLAN 2 - CASH</h2>
                
                <div class="space-y-6 text-xl font-bold text-gray-900">
                    <div class="flex items-center"><span>Registration</span> <div class="dots"></div> <span>₱900</span></div>
                    <div class="flex items-center"><span>Miscellaneous</span> <div class="dots"></div> <span>₱2,600</span></div>
                    
                    <div class="bg-orange-400 rounded-xl p-4 my-4 flex flex-col space-y-1">
                        <div class="flex items-center"><span>July - April (10mos.)</span> <div class="dots border-black"></div> <span>₱10,000</span></div>
                        <div class="flex items-center"><span>Less 10% discount</span> <div class="dots border-black"></div> <span>-₱1,000</span></div>
                    </div>

                    <div class="flex items-center border-b-4 border-black pb-2 justify-end">
                        <span class="mr-12">₱9,000</span>
                    </div>
                </div>

                <div class="mt-6 self-end text-right">
                    <div class="bg-red-700 text-white text-3xl font-black px-6 py-2 rounded-md shadow-lg italic">
                        = ₱12,500
                    </div>
                </div>
            </div>

        </div>
    </main>

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