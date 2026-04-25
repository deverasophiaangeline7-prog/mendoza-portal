@props(['href', 'icon', 'active' => false])

@php
    // If the link is active, it gets the orange background.
    // If it is inactive, it gets the standard red hover animation.
    // We put the "transition-all duration-300 ease-in-out" here so it's perfectly consistent!
    $classes = $active
                ? 'flex items-center p-3 space-x-3 bg-orange-400 text-black font-bold mx-2 rounded-lg transition-all duration-300 ease-in-out'
                : 'flex items-center p-3 space-x-3 text-white hover:bg-red-800 hover:pl-5 transition-all duration-300 ease-in-out';
@endphp

<li>
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        <i class="{{ $icon }} w-6"></i>
        <span>{{ $slot }}</span>
    </a>
</li>