@php
    $homeTitle = \App\Models\SiteContent::where('page', 'home')->where('key', 'hero_title')->value('value') ?? 'Welcome to Mendoza Academy, Inc.';
    $bg1 = \App\Models\SiteContent::where('page', 'home')->where('key', 'bg_1')->value('value') ?? 'images/HomePageBG.png';
    $bg2 = \App\Models\SiteContent::where('page', 'home')->where('key', 'bg_2')->value('value') ?? 'images/HomePageBG2.png';
    $bg3 = \App\Models\SiteContent::where('page', 'home')->where('key', 'bg_3')->value('value') ?? 'images/HomePageBG3.png';
    $bg4 = \App\Models\SiteContent::where('page', 'home')->where('key', 'bg_4')->value('value') ?? 'images/HomePageBG4.png';
    $bg5 = \App\Models\SiteContent::where('page', 'home')->where('key', 'bg_5')->value('value') ?? 'images/HomePageBG5.png';

    // Helper to format image asset path (handles storage uploads vs default assets)
    $getImageUrl = function($path) {
        return str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
    };
@endphp

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
            
            <!-- Background Image -->
            <img id="sliderImage" 
                 src="{{ $getImageUrl($bg1) }}" 
                 alt="Mendoza Academy" 
                 style="opacity: 1;"
                 class="absolute inset-0 w-full h-full object-cover">

            <!-- Dark overlay for better text visibility -->
            <div class="absolute inset-0 bg-black/30 pointer-events-none"></div>

            <!-- Left Arrow -->
            <button onclick="window.prevImage()" class="absolute left-4 top-1/2 -translate-y-1/2 bg-[#b52b2b] text-white px-4 py-3 rounded hover:bg-red-800 z-20 transition shadow-md">
                <i class="fas fa-chevron-left text-xl pointer-events-none"></i>
            </button>
            
            <!-- Centered Text -->
            <div class="relative z-30 text-center px-16 w-full pointer-events-none">
                <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white banner-text-shadow tracking-tight">
                    {{ $homeTitle }}
                </h2>
            </div>

            <!-- Right Arrow -->
            <button onclick="window.nextImage()" class="absolute right-4 top-1/2 -translate-y-1/2 bg-[#b52b2b] text-white px-4 py-3 rounded hover:bg-red-800 z-20 transition shadow-md">
                <i class="fas fa-chevron-right text-xl pointer-events-none"></i>
            </button>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const images = [
                "{{ $getImageUrl($bg1) }}",
                "{{ $getImageUrl($bg2) }}",
                "{{ $getImageUrl($bg3) }}",
                "{{ $getImageUrl($bg4) }}",
                "{{ $getImageUrl($bg5) }}"
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
                if (!slider || isTransitioning) return;
                
                isTransitioning = true;
                slider.style.opacity = 0;
                
                setTimeout(() => {
                    slider.src = images[currentIndex];
                    slider.style.opacity = 1;
                    
                    setTimeout(() => {
                        isTransitioning = false;
                    }, 300);
                }, 300); 
            }

            window.nextImage = function() {
                if (isTransitioning) return;
                currentIndex = (currentIndex + 1) % images.length;
                updateImage();
                resetInterval();
            };

            window.prevImage = function() {
                if (isTransitioning) return;
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                updateImage();
                resetInterval();
            };

            function resetInterval() {
                clearInterval(slideInterval);
                slideInterval = setInterval(window.nextImage, 8000);
            }

            resetInterval();
        });
    </script>

</x-guest-layout>