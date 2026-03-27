<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy, Inc.</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient {
            background: linear-gradient(to right, #d32f2f, #8b0000);
        }
        .nav-active {
            background-color: #ffb74d;
            color: #fff;
            border-radius: 9999px;
        }
        /* Controls the smooth fade transition between images */
        #sliderImage {
            transition: opacity 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100">

    <header class="hero-gradient text-white py-4 px-6 shadow-lg">
        <div class="container mx-auto flex flex-wrap justify-between items-center">
            
            <div class="flex items-center space-x-3">
                <div class="p-1 rounded shadow-sm">
                    <img src="{{ asset('images/MAILogo.png') }}" alt="Logo" class="h-10 w-10">
                </div>
                <h1 class="text-2xl font-bold tracking-tight uppercase">Mendoza Academy, Inc.</h1>
            </div>

            <nav class="hidden md:flex items-center space-x-8 font-medium">
    <a href="{{ url('/') }}" class="nav-active px-6 py-2">Home</a>
    
    <a href="#" class="hover:text-red-200 transition">About</a>
    <a href="#" class="hover:text-red-200 transition">Tuition Fees</a>
    <a href="#" class="hover:text-red-200 transition">FAQs</a>
    
    <a href="{{ route('login') }}" class="bg-orange-400 text-red-900 px-6 py-2 rounded-full font-black shadow-md hover:bg-orange-300 transition">SMS / LOG IN</a>
</nav>
        </div>
    </header>

    <main class="relative">
        <div class="relative w-full h-[500px] overflow-hidden bg-black">
            
            <img id="sliderImage" 
                 src="{{ asset('images/HomePageBG.png') }}" 
                 alt="Mendoza Academy" 
                 class="w-full h-full object-cover opacity-100">

            <div class="absolute inset-0 bg-black/10"></div>

            <button onclick="prevImage()" class="absolute left-4 top-1/2 -translate-y-1/2 bg-red-700/80 text-white p-4 rounded-lg hover:bg-red-800 z-10 transition shadow-md">
                <i class="fas fa-chevron-left text-2xl"></i>
            </button>
            
            <button onclick="nextImage()" class="absolute right-4 top-1/2 -translate-y-1/2 bg-red-700/80 text-white p-4 rounded-lg hover:bg-red-800 z-10 transition shadow-md">
                <i class="fas fa-chevron-right text-2xl"></i>
            </button>
        </div>
    </main>

    <section class="container mx-auto py-12 px-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div class="bg-white p-8 rounded-lg shadow-md border-t-4 border-red-600">
                <h3 class="text-xl font-bold mb-2">ENROLL NOW!</h3>
                <p class="text-gray-600">Enrolment is now ongoing for SY 2026-2027.</p>
            </div>
            <div class="bg-white p-8 rounded-lg shadow-md border-t-4 border-red-600">
                <h3 class="text-xl font-bold mb-2">COURSES OFFERED</h3>
                <p class="text-gray-600">Kindergarten to Elementary</p>
            </div>
            <div class="bg-white p-8 rounded-lg shadow-md border-t-4 border-red-600">
                <h3 class="text-xl font-bold mb-2">SUMMER CLASS</h3>
                <p class="text-gray-600">One-on-one tutoring from April 13, 2026 - May 13, 2026</p>
            </div>
        </div>
    </section>

    <script>
        // Array containing the paths to your 5 background images
        const images = [
            "{{ asset('images/HomePageBG.png') }}",
            "{{ asset('images/HomePageBG2.png') }}",
            "{{ asset('images/HomePageBG3.png') }}",
            "{{ asset('images/HomePageBG4.png') }}",
            "{{ asset('images/HomePageBG5.png') }}"
        ];

        let currentIndex = 0;
        const sliderImage = document.getElementById('sliderImage');

        function updateImage() {
            // Start the fade out
            sliderImage.style.opacity = 0;
            
            // Wait for 300ms (matching CSS transition), then change image and fade in
            setTimeout(() => {
                sliderImage.src = images[currentIndex];
                sliderImage.style.opacity = 1;
            }, 300); 
        }

        function nextImage() {
            // Loop back to index 0 after the 5th image
            currentIndex = (currentIndex + 1) % images.length;
            updateImage();
        }

        function prevImage() {
            // Loop back to the 5th image if going back from the 1st
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            updateImage();
        }

        // Automatic transition every 8 seconds
        setInterval(nextImage, 8000);
    </script>

</body>
</html>