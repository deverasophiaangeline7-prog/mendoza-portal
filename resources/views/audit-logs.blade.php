@extends('layouts.navigation')

@section('title', 'System Audit Logs')

@section('content')
<main class="p-8 flex-1 overflow-y-auto bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex justify-between items-start mb-10">
            
            <div class="text-left">
                <h2 class="text-5xl font-black text-black uppercase tracking-tight" style="text-shadow: 2px 2px 0px #f59e0b;">
                    Activity Logs                    
                </h2>
                <h3 class="text-xl font-bold text-gray-600 mt-2 italic leading-none">
                    A complete record of all sensitive system actions.
                </h3>
            </div>

            <div class="flex items-center gap-4 pt-2">
                
                <form action="{{ route('admin.audit_logs') }}" method="GET" class="flex items-center gap-3">
                    <div class="relative">
                        <i class="fa-solid fa-filter absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Filter by name, action, or date..." 
                               class="w-80 pl-12 pr-4 py-3 border-[3px] border-black rounded-full font-bold shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all">
                    </div>

                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-600 text-black font-black px-6 py-3 rounded-full border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all uppercase text-sm">
                        GO
                    </button>
                </form>

                <button onclick="window.history.back()" class="text-red-600 text-5xl hover:scale-110 transition leading-none">
                    <i class="fa-solid fa-circle-left"></i>
                </button>
            </div>
            
        </div>

        <div class="bg-white border-[3px] border-black rounded-2xl overflow-hidden shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
            <table class="w-full text-left border-collapse min-w-max">
                <thead class="bg-[#f59e0b] border-b-[3px] border-black text-black uppercase">
                <tr>
                    <th class="p-4 border-r-[3px] border-black w-48 text-center font-black text-lg">Date & Time</th>
                    <th class="p-4 border-r-[3px] border-black w-64 text-center font-black text-lg">User</th>
                    <th class="p-4 border-r-[3px] border-black w-64 text-center font-black text-lg">Action</th>
                    <th class="p-4 font-black text-center text-lg">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y-[3px] divide-black">
                @forelse($logs as $log)
                    <tr class="hover:bg-yellow-50 transition-colors"> 
                        <td class="p-4 border-r-[3px] border-black font-bold text-sm text-center text-gray-800">
                            {{ $log->created_at->format('M d, Y') }} <br>
                            <span class="text-red-600">{{ $log->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="p-4 border-r-[3px] border-black font-black uppercase text-blue-600 break-all">
                            {{ $log->user?->username ?? 'SYSTEM' }}
                        </td>
                        <td class="p-4 border-r-[3px] border-black font-bold text-center">
                            <span class="bg-gray-200 border-2 border-black px-3 py-1 rounded-full text-xs uppercase tracking-wider font-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] inline-block">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="p-4 font-medium text-gray-800 text-lg">
                            {{ $log->description }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-10 text-center text-gray-500 font-bold text-2xl uppercase italic">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
            </table>
            
            <div class="mt-8 p-4 bg-gray-50 border-t-[3px] border-black">
                {{ $logs->appends(['search' => $search])->links() }}
            </div>
        </div>
    </div>
</main>
@endsection