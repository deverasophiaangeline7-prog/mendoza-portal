@extends('layouts.navigation')

@section('title', 'Site Management')

@section('content')
<div class="flex-1 bg-gray-50 relative p-4 md:p-8 flex flex-col items-center min-h-screen w-full pt-16 md:pt-8">
    <div class="w-full max-w-7xl mx-auto">
        
        <div x-data="{ tab: 'branding' }"
             style="border-top-color: var(--color-primary);"
             class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:p-8 border-t-8">
            
            {{-- HEADER --}}
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black text-gray-900 uppercase tracking-wide">
                    Site Management
                </h2>
            </div>

            @if (session('status'))
                <div class="mb-6 bg-green-600 text-white p-4 rounded-xl font-bold uppercase text-xs tracking-widest text-center shadow-md">
                    {{ session('status') }}
                </div>
            @endif

            {{-- TAB NAVIGATION --}}
            <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-4 mb-6">
                <button type="button" @click="tab = 'branding'"
                    :class="tab === 'branding' ? 'text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    :style="tab === 'branding' ? 'background-color: var(--color-primary);' : ''"
                    class="px-4 py-2.5 rounded-lg font-black text-xs uppercase tracking-widest transition-all flex items-center gap-2">
                    <i class="fa-solid fa-school"></i> Branding & Logos
                </button>

                <button type="button" @click="tab = 'colors'"
                    :class="tab === 'colors' ? 'text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    :style="tab === 'colors' ? 'background-color: var(--color-primary);' : ''"
                    class="px-4 py-2.5 rounded-lg font-black text-xs uppercase tracking-widest transition-all flex items-center gap-2">
                    <i class="fa-solid fa-palette"></i> Theme & Colors
                </button>

                <button type="button" @click="tab = 'navigation'"
                    :class="tab === 'navigation' ? 'text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    :style="tab === 'navigation' ? 'background-color: var(--color-primary);' : ''"
                    class="px-4 py-2.5 rounded-lg font-black text-xs uppercase tracking-widest transition-all flex items-center gap-2">
                    <i class="fa-solid fa-bars"></i> Navigation & Icons
                </button>

                <button type="button" @click="tab = 'content'"
                    :class="tab === 'content' ? 'text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    :style="tab === 'content' ? 'background-color: var(--color-primary);' : ''"
                    class="px-4 py-2.5 rounded-lg font-black text-xs uppercase tracking-widest transition-all flex items-center gap-2">
                    <i class="fa-solid fa-globe"></i> Public Pages
                </button>
            </div>

            {{-- MAIN FORM --}}
            <form method="POST" action="{{ route('admin.content.update') }}" enctype="multipart/form-data" class="space-y-8">
                @csrf

                {{-- ========================================== --}}
                {{-- TAB 1: BRANDING & LOGOS                    --}}
                {{-- ========================================== --}}
                <div x-show="tab === 'branding'" x-cloak class="space-y-6">
                    <div>
                        <label class="block font-black text-xs text-gray-700 uppercase tracking-widest mb-1">School / System Name</label>
                        <input type="text" name="settings[school_name]" 
                               value="{{ $settings['school_name'] ?? 'Mendoza Academy, Inc.' }}"
                               class="w-full border border-gray-300 px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-900">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Main Logo --}}
                        <div class="p-4 border border-gray-200 rounded-xl bg-gray-50 flex items-center gap-4">
                            <img src="{{ asset($settings['logo_path'] ?? 'images/MAILogo.png') }}"
                                 alt="Current Logo"
                                 class="h-16 w-16 object-contain bg-white p-1.5 rounded-lg border shadow-sm">
                            <div class="flex-1">
                                <label class="block font-black text-xs text-gray-700 uppercase tracking-widest mb-1">Main School Logo</label>
                                <input type="file" name="logo_file" accept="image/*"
                                       class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-[#6D0101] hover:file:bg-red-100">
                            </div>
                        </div>

                        {{-- Favicon --}}
                        <div class="p-4 border border-gray-200 rounded-xl bg-gray-50 flex items-center gap-4">
                            <img src="{{ asset($settings['favicon_path'] ?? 'images/MAILogo.png') }}"
                                 alt="Current Favicon"
                                 class="h-16 w-16 object-contain bg-white p-1.5 rounded-lg border shadow-sm">
                            <div class="flex-1">
                                <label class="block font-black text-xs text-gray-700 uppercase tracking-widest mb-1">Browser Tab Favicon</label>
                                <input type="file" name="favicon_file" accept="image/*"
                                       class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-[#6D0101] hover:file:bg-red-100">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- TAB 2: THEME & COLORS                      --}}
                {{-- ========================================== --}}
                <div x-show="tab === 'colors'" x-cloak class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @php
                            $colorFields = [
                                'primary_color' => ['label' => 'Header / Primary Color', 'default' => '#6d0101'],
                                'navbar_bg'     => ['label' => 'Sidebar Background',     'default' => '#6d0101'],
                                'navbar_text'   => ['label' => 'Sidebar Text Color',     'default' => '#ffffff'],
                                'accent_color'  => ['label' => 'Accent / Highlight',     'default' => '#fb923c'],
                            ];
                        @endphp

                        @foreach($colorFields as $key => $meta)
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50"
                                 x-data="{ color: '{{ $settings[$key] ?? $meta['default'] }}' }">
                                <label class="block font-black text-xs text-gray-700 uppercase tracking-widest mb-2">
                                    {{ $meta['label'] }}
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="settings[{{ $key }}]" x-model="color"
                                           class="h-11 w-14 rounded cursor-pointer border border-gray-300 bg-white p-1">
                                    <input type="text" x-model="color" readonly
                                           class="w-full text-xs font-mono uppercase bg-white border border-gray-300 rounded px-2.5 py-2 text-gray-700">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="text-xs text-amber-900 font-semibold">
                            <i class="fa-solid fa-rotate-left mr-1"></i>
                            Want to restore the original Mendoza Academy maroon & white theme, logo, and default menu icons?
                        </div>
                        <button type="button"
                                onclick="if(confirm('Reset all branding, colors, and navigation back to default?')) document.getElementById('reset-settings-form').submit();"
                                class="shrink-0 bg-white text-red-700 border-2 border-red-700 font-black py-2 px-4 rounded-full text-xs uppercase tracking-widest hover:bg-red-50 transition-all">
                            Reset to Default
                        </button>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- TAB 3: NAVIGATION & ICONS                  --}}
                {{-- ========================================== --}}
                <div x-show="tab === 'navigation'" x-cloak class="space-y-4">
                    <p class="text-xs text-gray-500 font-semibold">
                        Rename sidebar labels or click any icon to swap it. Routes remain locked so links never break.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($settings['nav_items'] ?? [] as $navKey => $navItem)
                            <div x-data="{ openPicker: false, selectedIcon: '{{ $navItem['icon'] }}' }"
                                 class="p-4 border border-gray-200 rounded-xl bg-gray-50 relative">
                                <label class="block font-black text-[10px] text-gray-500 uppercase tracking-widest mb-2">
                                    {{ str_replace('_', ' ', $navKey) }}
                                </label>

                                <div class="flex items-center gap-3">
                                    {{-- Icon Picker Trigger --}}
                                    <button type="button" @click="openPicker = !openPicker"
                                            title="Change icon"
                                            class="h-11 w-11 shrink-0 bg-white border border-gray-300 rounded-lg flex items-center justify-center text-lg hover:border-red-800 transition-colors"
                                            style="color: var(--color-primary);">
                                        <i :class="selectedIcon"></i>
                                    </button>

                                    <input type="hidden" name="nav_items[{{ $navKey }}][icon]" :value="selectedIcon">

                                    {{-- Label Input --}}
                                    <input type="text" name="nav_items[{{ $navKey }}][label]"
                                           value="{{ $navItem['label'] }}"
                                           class="w-full border border-gray-300 px-3 py-2 rounded-md shadow-sm text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-red-900">
                                </div>

                                {{-- Curated Icon Picker Dropdown --}}
                                <div x-show="openPicker" @click.away="openPicker = false" x-cloak
                                     class="absolute left-4 top-20 z-30 bg-white border-2 border-gray-300 rounded-xl shadow-xl p-3 w-72">
                                    <div class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Select an Icon</div>
                                    <div class="grid grid-cols-6 gap-2">
                                        @foreach($availableIcons as $iconClass)
                                            <button type="button"
                                                    @click="selectedIcon = '{{ $iconClass }}'; openPicker = false"
                                                    :class="selectedIcon === '{{ $iconClass }}' ? 'bg-red-100 border-red-800 text-red-900' : 'bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100'"
                                                    class="h-9 w-9 rounded-lg border flex items-center justify-center transition-colors">
                                                <i class="{{ $iconClass }}"></i>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- TAB 4: PUBLIC PAGES (EXISTING FORM)        --}}
                {{-- ========================================== --}}
                <div x-show="tab === 'content'" x-cloak class="space-y-8">
                    <!-- HOME PAGE SECTION -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-black text-[#6D0101] uppercase mb-4">1. Home Page</h3>
                        
                        <div class="mb-4">
                            <label class="block font-black text-xs text-gray-700 uppercase tracking-widest mb-1">Hero Title</label>
                            <input type="text" name="content[home][hero_title]" 
                                value="{{ $contents->get('home')?->where('key', 'hero_title')?->first()?->value ?? 'Welcome to Mendoza Academy, Inc.' }}" 
                                class="w-full border-gray-300 focus:border-[#6D0101] focus:ring-[#6D0101] rounded-md shadow-sm">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            @foreach(['bg_1' => 'Slide 1', 'bg_2' => 'Slide 2', 'bg_3' => 'Slide 3', 'bg_4' => 'Slide 4', 'bg_5' => 'Slide 5'] as $key => $label)
                                <div>
                                    <label class="block font-bold text-[10px] text-gray-600 uppercase tracking-widest mb-1">{{ $label }} Image</label>
                                    <input type="file" name="images[home][{{ $key }}]" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-[#6D0101] hover:file:bg-red-100">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- ABOUT PAGE SECTION -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-black text-[#6D0101] uppercase mb-4">2. About Page</h3>
                        
                        <div class="mb-4">
                            <label class="block font-black text-xs text-gray-700 uppercase tracking-widest mb-1">Mission Text</label>
                            <textarea name="content[about][mission]" rows="3" class="w-full border-gray-300 focus:border-[#6D0101] focus:ring-[#6D0101] rounded-md shadow-sm">{{ $contents->get('about')?->where('key', 'mission')?->first()?->value ?? 'Mendoza Academy, Inc. aims to develop the inner potentials of individuals and mold them into young adults who are intellectually and academically competent appreciative of the cultural heritage of their environment, disciplined and determined to contribute their best effort as future leaders to the community with a well-bred conscience to distinguish what is right and wrong and their righteous commitment to the Lord Almighty.' }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block font-black text-xs text-gray-700 uppercase tracking-widest mb-1">Vision Text</label>
                            <textarea name="content[about][vision]" rows="3" class="w-full border-gray-300 focus:border-[#6D0101] focus:ring-[#6D0101] rounded-md shadow-sm">{{ $contents->get('about')?->where('key', 'vision')?->first()?->value ?? 'As a private institution, Mendoza Academy, Inc. perceives itself that through competency in imparting discipline, quality education, full understanding of the individual differences among learners and honing them for their total development to be physically, mentally, socially, emotionally and spiritually fit, is conclusive of producing holistic graduates who are God-fearing aware of their worth and dignity.' }}</textarea>
                        </div>
                    </div>

                    <!-- COURSES PAGE SECTION -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-black text-[#6D0101] uppercase mb-4">3. Courses Page</h3>
                        
                        <div class="mb-4">
                            <label class="block font-black text-xs text-gray-700 uppercase tracking-widest mb-1">Pre-Elementary Sub-text (separate with |)</label>
                            <input type="text" name="content[courses][pre_elementary]" 
                                value="{{ $contents->get('courses')?->where('key', 'pre_elementary')?->first()?->value ?? 'Nursery | Kindergarten | Preparatory' }}" 
                                class="w-full border-gray-300 focus:border-[#6D0101] focus:ring-[#6D0101] rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label class="block font-black text-xs text-gray-700 uppercase tracking-widest mb-1">Elementary Sub-text</label>
                            <input type="text" name="content[courses][elementary]" 
                                value="{{ $contents->get('courses')?->where('key', 'elementary')?->first()?->value ?? 'Grades 1 - 6' }}" 
                                class="w-full border-gray-300 focus:border-[#6D0101] focus:ring-[#6D0101] rounded-md shadow-sm">
                        </div>
                    </div>

                    <!-- FAQS PAGE SECTION -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-black text-[#6D0101] uppercase mb-4">4. FAQs Page</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            @php
                                $faqDefaults = [
                                    'q1' => 'What grade levels do you offer?',
                                    'a1' => 'Pre-elementary (Nursery, Kinder, and Preparatory) to Elementary (Grades 1-6)',
                                    'q2' => 'Where is the school located?',
                                    'a2' => 'Blk. 9 Lot 88 Mapagkawanggawa St. Purok 4, Lupang Arenda, Sta. Ana, Taytay, Rizal',
                                    'q3' => 'What are your office hours?',
                                    'a3' => 'Monday to Friday (8:00AM - 5:00PM)',
                                    'q4' => 'What are the admission requirements?'
                                ];
                            @endphp
                            @foreach($faqDefaults as $key => $defaultVal)
                                <div>
                                    <label class="block font-black text-xs text-gray-700 uppercase tracking-widest mb-1">{{ strtoupper($key) }} Text / Heading</label>
                                    <input type="text" name="content[faqs][{{ $key }}]" 
                                        value="{{ $contents->get('faqs')?->where('key', $key)?->first()?->value ?? $defaultVal }}" 
                                        class="w-full border-gray-300 focus:border-[#6D0101] focus:ring-[#6D0101] rounded-md shadow-sm">
                                </div>
                            @endforeach
                        </div>

                        <!-- Admission Requirements HTML Answer -->
                        @php
                        $defaultA4Html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left max-w-4xl mx-auto px-2">
                        <div class="space-y-6">
                            <div class="bg-[#b26905] rounded-3xl p-6 shadow-md border-2 border-black">
                                <h4 class="font-black text-white text-lg mb-3 uppercase">For incoming Kindergarten:</h4>
                                <div class="flex items-start gap-3 text-white font-bold">
                                    <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center left-center mt-1 text-xs font-black">✔</span>
                                    <p class="text-sm text-left">Must be five (5) years old on or before August 31, 2026. (Reference: DepEd Order No. 47 s. 2016/ DepEd Order No. 20, s. 2018)</p>
                                </div>
                            </div>
                            <div class="bg-[#b26905] rounded-3xl p-6 shadow-md border-2 border-black">
                                <h4 class="font-black text-white text-lg mb-3 uppercase">Document to be submitted:</h4>
                                <div class="flex items-start gap-3 text-white font-bold">
                                    <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center left-center mt-1 text-xs font-black">✔</span>
                                    <p class="text-sm text-left">Photocopy of Birth Certificate [In the absence of PSA/NSO Birth Certificate, submit a photocopy of Baptismal or Barangay Certificate.]</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div class="bg-[#b26905] rounded-3xl p-6 shadow-md border-2 border-black">
                                <h4 class="font-black text-white text-lg mb-3 uppercase">For incoming Grade 1:</h4>
                                <div class="flex items-start gap-3 text-white font-bold">
                                    <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center left-center mt-1 text-xs font-black">✔</span>
                                    <p class="text-sm text-left">Must be Kindergarten Completer</p>
                                </div>
                            </div>
                            <div class="bg-[#b26905] rounded-3xl p-6 shadow-md border-2 border-black">
                                <h4 class="font-black text-white text-lg mb-3 uppercase">For more information:</h4>
                                <div class="space-y-4 text-white font-bold text-sm text-left">
                                    <div class="flex items-start gap-3">
                                        <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center left-center text-xs font-black">✔</span>
                                        <span>Visit our school or send a message to our official FB Page.</span>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <span class="bg-white text-green-600 rounded-full h-5 w-5 flex-shrink-0 flex items-center left-center text-xs font-black">✔</span>
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
                        </div>
                    </div>';
                        @endphp

                        <div>
                            <label class="block font-black text-xs text-gray-700 uppercase tracking-widest mb-1">A4 HTML Content (Admission Requirements)</label>
                            <p class="text-[10px] text-red-600 font-bold mb-2 uppercase">Warning: This contains raw HTML for the styled cards. Edit text carefully!</p>
                            <textarea name="content[faqs][a4]" rows="12" class="w-full border-gray-300 focus:border-[#6D0101] focus:ring-[#6D0101] rounded-md shadow-sm text-xs font-mono leading-tight whitespace-pre-wrap">{{ $contents->get('faqs')?->where('key', 'a4')?->first()?->value ?? $defaultA4Html }}</textarea>
                        </div>
                    </div>

                    <!-- SMS / LOG IN PAGE SECTION -->
                    <div class="pb-4">
                        <h3 class="text-lg font-black text-[#6D0101] uppercase mb-4">5. SMS / Log In Page</h3>
                        
                        <div class="mb-4">
                            <label class="block font-black text-xs text-gray-700 uppercase tracking-widest mb-1">Main Banner Heading HTML</label>
                            <input type="text" name="content[login][heading]" 
                                value="{{ $contents->get('login')?->where('key', 'heading')?->first()?->value ?? 'STUDENT MONITORING<br><span class=\"text-white\">SYSTEM</span>' }}" 
                                class="w-full border-gray-300 focus:border-[#6D0101] focus:ring-[#6D0101] rounded-md shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="submit"
                            style="background-color: var(--color-primary);"
                            class="text-white font-black py-3 px-8 rounded-full shadow-[0px_4px_0px_0px_#3a0000] hover:opacity-90 transition-all uppercase text-xs tracking-widest border-2 border-black active:translate-y-1 active:shadow-[0px_1px_0px_0px_#3a0000]">
                        Save Changes
                    </button>
                </div>

            </form>

            {{-- HIDDEN RESET FORM (Must stay outside the main form) --}}
            <form id="reset-settings-form" method="POST" action="{{ route('admin.content.reset') }}" class="hidden">
                @csrf
            </form>

        </div>
    </div>
</div>
@endsection