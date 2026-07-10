<x-guest-layout>
    
    <style>
        /* Controls the smooth fade transition between images */
        #sliderImage {
            transition: opacity 0.5s ease-in-out;
        }
    </style>

    <main class="relative flex-grow flex flex-col w-full h-full">
        <div class="relative w-full flex-grow min-h-[500px] overflow-hidden bg-black">
            
            <img id="sliderImage" 
                 src="{{ asset('images/HomePageBG.png') }}" 
                 alt="Mendoza Academy" 
                 class="absolute inset-0 w-full h-full object-cover opacity-100">

            <div class="absolute inset-0 bg-black/10"></div>

            <button onclick="prevImage()" class="absolute left-4 top-1/2 -translate-y-1/2 bg-red-700/80 text-white p-4 rounded-lg hover:bg-red-800 z-10 transition shadow-md">
                <i class="fas fa-chevron-left text-2xl"></i>
            </button>
            
            <button onclick="nextImage()" class="absolute right-4 top-1/2 -translate-y-1/2 bg-red-700/80 text-white p-4 rounded-lg hover:bg-red-800 z-10 transition shadow-md">
                <i class="fas fa-chevron-right text-2xl"></i>
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