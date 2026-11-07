@extends('layouts.navigation')

@section('title', 'Edit Homepage')

@section('content')
<div class="flex-1 bg-gray-50 relative p-4 md:p-8 flex flex-col items-center min-h-screen w-full pt-16 md:pt-8">
    <div class="w-full max-w-7xl mx-auto">
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:p-8 border-t-8 border-[#6D0101]">
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black text-gray-900 uppercase tracking-wide">
                    Edit Homepage & Public Content
                </h2>
                <a href="{{ route('account.management') }}" class="bg-gray-500 text-white font-bold px-4 py-2 rounded-full text-xs uppercase tracking-widest hover:bg-gray-600 transition">
                    Back to Account Management
                </a>
            </div>

            @if (session('status'))
                <div class="mb-6 bg-green-600 text-white p-4 rounded-xl font-bold uppercase text-xs tracking-widest text-center shadow-md">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.content.update') }}" enctype="multipart/form-data" class="space-y-8">
                @csrf

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
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                <!-- SUBMIT BUTTON -->
                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-[#6D0101] text-white font-black py-3 px-8 rounded-full shadow-[0px_4px_0px_0px_#3a0000] hover:bg-red-900 transition-all uppercase text-xs tracking-widest border-2 border-black active:translate-y-1 active:shadow-[0px_1px_0px_0px_#3a0000]">
                        Save Changes
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection