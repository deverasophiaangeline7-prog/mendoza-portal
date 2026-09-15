<x-guest-layout>
    
    <style>
        /* Controls the smooth fade transition between images */
        #sliderImage {
            transition: opacity 0.5s ease-in-out;
        }
        /* Adds the red shadow effect to the text */
        .banner-text-shadow {
            text-shadow: 3px 3px 0px #6d0101, -1px -1px 0 #6d0101, 1px -1px 0 #6d0101, -1px 1px 0 #6d0101, 1px 1px 0 #6d0101;
        }
    </style>

    <main class="relative flex-grow flex flex-col w-full h-full">
        <div class="relative w-full flex-grow min-h-[500px] overflow-hidden bg-black flex items-center justify-center">
            
            <!-- Background Image -->
            <img id="sliderImage" 
                 src="{{ asset('images/HomePageBG.png') }}" 
                 alt="Mendoza Academy" 
                 class="absolute inset-0 w-full h-full object-cover opacity-100">

            <!-- Dark overlay for better text visibility -->
            <div class="absolute inset-0 bg-black/30"></div>

            <!-- Left Arrow -->
            <button onclick="prevImage()" class="absolute left-4 top-1/2 -translate-y-1/2 bg-[#b52b2b] text-white px-4 py-3 rounded hover:bg-red-800 z-20 transition shadow-md">
                <i class="fas fa-chevron-left text-xl"></i>
            </button>
            
            <!-- Centered Text -->
            <div class="relative z-30 text-center px-16 w-full">
                <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white banner-text-shadow tracking-tight">
                    Welcome to Mendoza Academy, Inc.
                </h2>
            </div>

            <!-- Right Arrow -->
            <button onclick="nextImage()" class="absolute right-4 top-1/2 -translate-y-1/2 bg-[#b52b2b] text-white px-4 py-3 rounded hover:bg-red-800 z-20 transition shadow-md">
                <i class="fas fa-chevron-right text-xl"></i>
            </button>
        </div>
    </main>

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

</x-guest-layout>