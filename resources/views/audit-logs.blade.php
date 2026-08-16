@extends('layouts.navigation')

@section('title', 'System Audit Logs')

@section('content')
<main class="p-4 sm:p-6 lg:p-8 flex-1 min-w-0 overflow-y-auto bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-3xl sm:text-4xl font-black text-black uppercase tracking-tight" style="text-shadow: 2px 2px 0px #f59e0b;">
                    Activity Logs                    
                </h2>
                <h3 class="text-sm sm:text-base font-bold text-gray-600 mt-1 italic">
                    A complete record of all sensitive system actions.
                </h3>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                <form id="search-form" action="{{ route('admin.audit_logs') }}" method="GET" class="flex items-center gap-2 flex-1 sm:flex-none">
                    <div class="relative w-full sm:w-72 sm:w-96">
                        <i class="fa-solid fa-filter absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" 
                               id="search-input"
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Filter by name, action, or date..." 
                               class="w-full pl-10 pr-3 py-2 border-[3px] border-black rounded-full font-bold text-xs sm:text-sm shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all">
                    </div>
                </form>

                <!-- Changed from button to an anchor tag pointing to the Account Management route -->
                <a href="{{ route('account.management') }}" class="text-red-600 text-3xl sm:text-4xl hover:scale-110 transition leading-none shrink-0 inline-block">
                    <i class="fa-solid fa-circle-left"></i>
                </a>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white border-[3px] border-black rounded-2xl overflow-x-auto shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
            <table class="w-full text-left border-collapse table-auto min-w-[700px]">
                <thead class="bg-[#f59e0b] border-b-[3px] border-black text-black uppercase">
                    <tr>
                        <th class="p-3 border-r-[3px] border-black w-36 text-center font-black text-xs sm:text-sm">Date & Time</th>
                        <th class="p-3 border-r-[3px] border-black w-48 text-center font-black text-xs sm:text-sm">User</th>
                        <th class="p-3 border-r-[3px] border-black w-44 text-center font-black text-xs sm:text-sm">Action</th>
                        <th class="p-3 font-black text-left text-xs sm:text-sm">Description</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="divide-y-[3px] divide-black">
                    @forelse($logs as $log)
                        @php
                            // 1. Determine the proper display name
                            $displayName = 'SYSTEM';
                            if ($log->user && $log->user->teacher) {
                                $displayName = $log->user->teacher->first_name . ' ' . $log->user->teacher->last_name;
                            } elseif ($log->user) {
                                $displayName = explode('@', $log->user->username)[0];
                            }

                            // 2. Replace the raw email/username in the description with the display name
                            $formattedDescription = $log->description;
                            if ($log->user && $log->user->username) {
                                $formattedDescription = str_ireplace($log->user->username, $displayName, $log->description);
                            }
                        @endphp
                        
                        <tr> 
                            <td class="p-3 border-r-[3px] border-black font-bold text-xs text-center text-gray-800 whitespace-nowrap">
                                {{ $log->created_at->format('M d, Y') }} <br>
                                <span class="text-red-600 font-extrabold">{{ $log->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="p-3 border-r-[3px] border-black font-black text-xs uppercase text-blue-600 break-all max-w-[180px]">
                                {{ $displayName }}
                            </td>
                            <td class="p-3 border-r-[3px] border-black font-bold text-center">
                                <span class="text-[10px] sm:text-xs uppercase tracking-wider font-black text-gray-800 inline-block">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="p-3 font-medium text-gray-800 text-xs sm:text-sm break-words leading-relaxed">
                                {{ $formattedDescription }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-500 font-bold text-lg sm:text-xl uppercase italic">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div id="pagination-container" class="p-3 sm:p-4 bg-gray-50 border-t-[3px] border-black">
                {{ $logs->appends(['search' => $search ?? request('search')])->links() }}
            </div>
        </div>

    </div>
</main>

<!-- Live Search Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const form = document.getElementById('search-form');
        const tableBody = document.getElementById('table-body');
        const paginationContainer = document.getElementById('pagination-container');
        
        let debounceTimer;

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                
                debounceTimer = setTimeout(() => {
                    const searchQuery = this.value;
                    const url = new URL(form.action);
                    
                    if (searchQuery) {
                        url.searchParams.set('search', searchQuery);
                    } else {
                        url.searchParams.delete('search');
                    }

                    // Fetch without XMLHttpRequest header to ensure standard HTML response
                    fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.text();
                    })
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        const newTableBody = doc.getElementById('table-body');
                        const newPagination = doc.getElementById('pagination-container');
                        
                        if (newTableBody && tableBody) {
                            tableBody.innerHTML = newTableBody.innerHTML;
                        }
                        if (newPagination && paginationContainer) {
                            paginationContainer.innerHTML = newPagination.innerHTML;
                        }
                        
                        window.history.pushState({}, '', url);
                    })
                    .catch(error => console.error('Search error:', error));
                }, 300); 
            });
        }
    });
</script>
@endsection