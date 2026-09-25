<x-guest-layout>
    
    <style>
        /* Controls the smooth fade transition between images */
        #sliderImage {
            transition: opacity 0.3s ease-in-out;
        }
        /* Adds the red shadow effect to the text */
        .banner-text-shadow {
            text-shadow: 3px 3px 0px #6d0101, -1px -1px 0 #6d0101, 1px -1px 0 #6d0101, -1px 1px 0 #6d0101, 1px 1px 0 #6d0101;
        }
    </style>

    <main class="relative flex-grow flex flex-col w-full h-full">
        <div class="relative w-full flex-grow min-h-[500px] overflow-hidden bg-black flex items-center justify-center">
            
            <!-- Background Image (Removed opacity-100 class to prevent CSS conflicts) -->
            <img id="sliderImage" 
                 src="{{ asset('images/HomePageBG.png') }}" 
                 alt="Mendoza Academy" 
                 style="opacity: 1;"
                 class="absolute inset-0 w-full h-full object-cover">

            <!-- Dark overlay for better text visibility -->
            <div class="absolute inset-0 bg-black/30 pointer-events-none"></div>

            <!-- Left Arrow (pointer-events-none added to the icon) -->
            <button onclick="window.prevImage()" class="absolute left-4 top-1/2 -translate-y-1/2 bg-[#b52b2b] text-white px-4 py-3 rounded hover:bg-red-800 z-20 transition shadow-md">
                <i class="fas fa-chevron-left text-xl pointer-events-none"></i>
            </button>
            
            <!-- Centered Text -->
            <div class="relative z-30 text-center px-16 w-full pointer-events-none">
                <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white banner-text-shadow tracking-tight">
                    Welcome to Mendoza Academy, Inc.
                </h2>
            </div>

            <!-- Right Arrow (pointer-events-none added to the icon) -->
            <button onclick="window.nextImage()" class="absolute right-4 top-1/2 -translate-y-1/2 bg-[#b52b2b] text-white px-4 py-3 rounded hover:bg-red-800 z-20 transition shadow-md">
                <i class="fas fa-chevron-right text-xl pointer-events-none"></i>
            </button>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Array containing the paths to your 5 background images
            const images = [
                "{{ asset('images/HomePageBG.png') }}",
                "{{ asset('images/HomePageBG2.png') }}",
                "{{ asset('images/HomePageBG3.png') }}",
                "{{ asset('images/HomePageBG4.png') }}",
                "{{ asset('images/HomePageBG5.png') }}"
            ];

            let currentIndex = 0;
            let slideInterval;
            let isTransitioning = false;

            // Preload images so they render instantly when called
            images.forEach(src => {
                const img = new Image();
                img.src = src;
            });

            function updateImage() {
                const slider = document.getElementById('sliderImage');
                // Prevent overlapping transitions or errors if element is missing
                if (!slider || isTransitioning) return;
                
                isTransitioning = true;
                
                // Start the fade out
                slider.style.opacity = 0;
                
                // Wait for 300ms (matching CSS transition), change image, and fade in
                setTimeout(() => {
                    slider.src = images[currentIndex];
                    slider.style.opacity = 1;
                    
                    // Unlock the transition after the fade-in completes
                    setTimeout(() => {
                        isTransitioning = false;
                    }, 300);
                }, 300); 
            }

            // Expose functions globally to the window so the HTML buttons can find them
            window.nextImage = function() {
                if (isTransitioning) return;
                currentIndex = (currentIndex + 1) % images.length;
                updateImage();
                resetInterval(); // Restart the 8-second timer
            };

            window.prevImage = function() {
                if (isTransitioning) return;
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                updateImage();
                resetInterval(); // Restart the 8-second timer
            };

            // Clear and restart the auto-slider to prevent jumping after a manual click
            function resetInterval() {
                clearInterval(slideInterval);
                slideInterval = setInterval(window.nextImage, 8000);
            }

            // Initialize the automatic transition
            resetInterval();
        });
    </script>

</x-guest-layout>