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
        .ribbon {
            position: relative;
            background: white;
            padding: 10px 60px;
            clip-path: polygon(10% 0, 90% 0, 100% 50%, 90% 100%, 10% 100%, 0% 50%);
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
    <a href="{{ url('about') }}" class="nav-active px-6 py-2">About</a>
    <a href="{{ url('tuitionfee') }}" class="hover:text-red-200 transition">Tuition Fees</a>
    <a href="{{ url('faqs') }}" class="hover:text-red-200 transition">FAQs</a>
    
    <a href="{{ route('login') }}" class="bg-orange-400 text-red-900 px-6 py-2 rounded-full font-black shadow-md hover:bg-orange-300 transition">SMS / LOG IN</a>
</nav>
        </div>
    </header>

    <main class= "relative flex-grow flex items-center justify-center py-20 bg-cover bg-center bg-no-repeat" 
          style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('{{ asset('images/about.jpg') }}');">
        
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl">
            
            <div class="flex flex-col items-center">
                <div class="ribbon text-red-700 font-black text-xl mb-4 shadow-md">MISSION</div>
                <div class="bg-white rounded-2xl p-8 shadow-xl text-center leading-relaxed text-gray-800 font-semibold h-full">
                    Mendoza Academy, Inc. aims to develop the inner potentials of individuals and mold them into young adults who are intellectually and academically competent appreciative of the cultural heritage of their environment, disciplined and determined to contribute their best effort as future leaders to the community with a well-bred conscience to distinguish what is right and wrong and their righteous commitment to the Lord Almighty.
                </div>
            </div>

            <div class="flex flex-col items-center">
                <div class="ribbon text-red-700 font-black text-xl mb-4 shadow-md">VISION</div>
                <div class="bg-white rounded-2xl p-8 shadow-xl text-center leading-relaxed text-gray-800 font-semibold h-full">
                    As a private institution, Mendoza Academy, Inc. perceives itself that through competency in imparting discipline, quality education, full understanding of the individual differences among learners and honing them for their total development to be physically, mentally, socially, emotionally and spiritually fit, is conclusive of producing holistic graduates who are God-fearing aware of their worth and dignity.
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