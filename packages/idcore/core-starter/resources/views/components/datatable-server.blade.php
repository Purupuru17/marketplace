@props([
    'url' => null,
    'columns' => [],
    'perPage' => 10,
    'perPageOptions' => [5, 10, 25, 50, 100],
    'searchable' => true,
    'showNumber' => true,
    'emptyMessage' => 'Belum ada data.',
    'method' => 'GET',
    'actionsHeader' => 'Aksi',
    'embedded' => true,
    'selectable' => false,
    'filters' => [],
])

@php
    $columns = collect($columns)->map(fn($col) => array_merge([
        'key' => null,
        'label' => '',
        'sortable' => false,
        'searchable' => false,
        'html' => false,
        'align' => 'center',
        'width' => null,
    ], $col))->all();
    $hasActions = isset($actions);
@endphp

<div x-data="{
    url: {{ Js::from($url) }},
    method: {{ Js::from($method) }},
    columns: @js($columns),
    perPage: @js((int) $perPage),
    perPageOptions: @js($perPageOptions),
    search: '',
    sortKey: null,
    sortDir: 'asc',
    page: 1,
    draw: 0,
    rows: @js([]),
    recordsTotal: 0,
    recordsFiltered: 0,
    loading: false,
    selectable: false,
    selected: [],
    filters: {},
    pendingFilters: {},
    showFilters: false,

    get totalPages() { return Math.max(1, Math.ceil(this.recordsFiltered / this.perPage)); },
    get from() { return this.recordsFiltered === 0 ? 0 : (this.page - 1) * this.perPage + 1; },
    get to() { return Math.min(this.page * this.perPage, this.recordsFiltered); },

    init() {
        this.fetchData();
    },

    buildQuery() {
        const params = new URLSearchParams();
        const start = (this.page - 1) * this.perPage;
        params.set('draw', ++this.draw);
        params.set('start', start);
        params.set('length', this.perPage);

        const searchableColumns = this.columns.filter(c => c.searchable !== false);
        const sortTarget = this.sortKey
            ? this.columns.findIndex(c => c.key === this.sortKey)
            : (searchableColumns[0] !== undefined ? this.columns.indexOf(searchableColumns[0]) : 0);

        params.set('search[value]', this.search);
        params.set('search[regex]', 'false');

        this.columns.forEach((col, i) => {
            params.set('columns[' + i + '][data]', col.key);
            params.set('columns[' + i + '][name]', col.key);
            params.set('columns[' + i + '][searchable]', col.searchable !== false ? 'true' : 'false');
            params.set('columns[' + i + '][orderable]', col.sortable !== false ? 'true' : 'false');
            params.set('columns[' + i + '][search][value]', '');
            params.set('columns[' + i + '][search][regex]', 'false');
        });

        if (sortTarget >= 0 && this.columns[sortTarget].sortable !== false) {
            params.set('order[0][column]', sortTarget);
            params.set('order[0][dir]', this.sortDir);
        }
        Object.entries(this.filters).forEach(([key, value]) => {
            if (value !== '' && value !== null && value !== undefined) {
                params.set(key, value);
            }
        });

        return params;
    },

    applyFilters() {
        this.filters = { ...this.pendingFilters }; // commit draft jadi aktif
        this.page = 1;                              // reset ke halaman 1, penting karena hasil filter beda total data
        this.fetchData();
    },

    async fetchData() {
        this.loading = true;
        try {
            const params = this.buildQuery();
            const opts = { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };
            const separator = this.url.includes('?') ? '&' : '?';
            const response = this.method === 'POST'
                ? await fetch(this.url, { ...opts, method: 'POST', body: params })
                : await fetch(this.url + separator + params.toString(), { ...opts, method: 'GET' });

            const json = await response.json();
            this.rows = json.data ?? [];
            this.recordsTotal = json.recordsTotal ?? 0;
            this.recordsFiltered = json.recordsFiltered ?? 0;
        } finally {
            this.loading = false;
        }
    },

    setSort(col) {
        if (!col.sortable) return;
        if (this.sortKey === col.key) {
            this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortKey = col.key;
            this.sortDir = 'asc';
        }
        this.page = 1;
        this.fetchData();
    },

    pageList() {
        const total = this.totalPages;
        const current = this.page;
        const list = [];
        const range = 2;
        for (let i = 1; i <= total; i++) {
            if (i === 1 || i === total || (i >= current - range && i <= current + range)) {
                list.push(i);
            } else if (list[list.length - 1] !== '...') {
                list.push('...');
            }
        }
        return list;
    },

    goTo(p) {
        if (typeof p === 'number' && p >= 1 && p <= this.totalPages) {
            this.page = p;
            this.fetchData();
        }
    },
    prev() { if (this.page > 1) { this.page--; this.fetchData(); } },
    next() { if (this.page < this.totalPages) { this.page++; this.fetchData(); } },
    doSearch() { 
        const len = this.search.trim().length;
        if (len === 0 || len >= 3) {
            this.page = 1;
            this.fetchData();
        }
    },
    changePerPage() { this.page = 1; this.fetchData(); },

    toggleAll(checked) {
        this.selected = checked ? this.rows.map(r => r.id) : [];
    },
    toggleRow(id) {
        const i = this.selected.indexOf(id);
        i === -1 ? this.selected.push(id) : this.selected.splice(i, 1);
    },
    isSelected(id) {
        return this.selected.includes(id);
    },
}"
class="overflow-hidden {{ $embedded ? '' : 'rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]' }}">

     {{-- SECTION 1: Filter panel — grid terpisah, gak nyatu sama toolbar --}}
    @if(isset($filters) && trim($filters))
        <div class="border-b border-gray-100 dark:border-gray-800">
            <button type="button" @click="showFilters = !showFilters"
                class="flex w-full items-center justify-between px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                <span class="inline-flex items-center gap-1.5">
                    @svg('heroicon-o-funnel', 'h-4 w-4')
                    Filter
                </span>
                @svg('heroicon-o-chevron-down', 'h-4 w-4')
            </button>
            <div x-show="showFilters" x-collapse class="px-5 pb-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {{ $filters }}
                </div>
            </div>
        </div>
    @endif

    {{-- SECTION 2: Toolbar — per-page & search, TIDAK BERUBAH dari sebelumnya --}}
    @if($searchable)
    <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span>Show</span>
            <x-idcore::select name="dt-per-page" x-model="perPage" @change="changePerPage()" :options="collect($perPageOptions)->mapWithKeys(fn($o) => [$o => $o])->all()" placeholder="" class="!w-20" />
            <span>entries</span>
        </div>
        <div class="w-full md:max-w-xs">
            <x-idcore::input
                x-model="search"
                @input.debounce.300ms="doSearch()"
                type="search"
                name="dtc-search"
                icon="magnifying-glass"
                placeholder="Search..."
                class="pr-8"
            />
        </div>
    </div>
    @endif

    <div class="relative max-w-full overflow-x-auto">
        <div x-show="loading" x-cloak class="absolute inset-0 z-10 flex items-center justify-center bg-white/60 dark:bg-gray-950/60">
            <svg class="h-10 w-10 animate-spin text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        <table {{ $attributes->merge(['class' => 'min-w-full  border-collapse']) }}>
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr class="transition hover:bg-gray-200 dark:hover:bg-gray-800/60">
                    @if($selectable)
                        <th class="border border-gray-200 px-5 py-4 w-10 text-center dark:border-gray-800">
                            <input type="checkbox" @change="toggleAll($event.target.checked)"
                                :checked="rows.length > 0 && selected.length === rows.length"
                                class="rounded border-gray-300">
                        </th>
                    @endif
                    @if($showNumber)
                        <th class="border border-gray-200 px-5 py-4 text-center text-theme-xs font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            No
                        </th>
                    @endif
                    @foreach($columns as $col)
                    @php
                        $alignClass = $col['align'] === 'center' ? 'text-center' : ($col['align'] === 'right' ? 'text-right' : 'text-left');
                    @endphp
                    <th @if($col['width']) style="width: {{ $col['width'] }}" @endif
                        class="border border-gray-200 px-5 py-4 {{ $alignClass }} text-theme-xs font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        @if($col['sortable'])
                            <button type="button" @click="setSort({{ Js::from($col) }})"
                                class="group inline-flex w-full items-center gap-1.5 transition hover:text-brand-600 dark:hover:text-brand-400">
                                <span class="flex-1 {{ $alignClass }}">{{ $col['label'] }}</span>
                                <span class="shrink-0 ml-auto h-4 w-4 text-gray-300 dark:text-gray-600">
                                    <span x-show="sortKey === {{ Js::from($col['key']) }} && sortDir === 'asc'" class="text-brand-500 dark:text-brand-400">
                                        @svg('heroicon-o-chevron-up', 'h-4 w-4')
                                    </span>
                                    <span x-show="sortKey === {{ Js::from($col['key']) }} && sortDir === 'desc'" class="text-brand-500 dark:text-brand-400">
                                        @svg('heroicon-o-chevron-down', 'h-4 w-4')
                                    </span>
                                    <span x-show="sortKey !== {{ Js::from($col['key']) }}">
                                        @svg('heroicon-m-chevron-up-down', 'h-4 w-4')
                                    </span>
                                </span>
                            </button>
                        @else
                            <span class="block {{ $alignClass }}">{{ $col['label'] }}</span>
                        @endif
                    </th>
                @endforeach
                    @if($hasActions)
                        <th class="border border-gray-200 px-5 py-4 text-center text-theme-xs font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400">{{ $actionsHeader }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, idx) in rows" :key="draw + '-' + idx">
                    <tr class="transition hover:bg-gray-100 dark:hover:bg-gray-800/60">
                        @if($selectable)
                            <td class="border border-gray-200 px-5 py-4 text-center dark:border-gray-800">
                                <input type="checkbox" :checked="isSelected(row.id)" @change="toggleRow(row.id)"
                                    class="rounded border-gray-300">
                            </td>
                        @endif
                        @if($showNumber)
                            <td class="border border-gray-200 px-5 py-4 text-center text-theme-sm text-gray-500 dark:border-gray-800 dark:text-gray-400" x-text="from + idx"></td>
                        @endif
                        <template x-for="col in columns" :key="col.key">
                            <td class="border border-gray-200 px-5 py-4 text-theme-sm text-gray-700 dark:border-gray-800 dark:text-gray-300"
                                :class="col.align === 'center' ? 'text-center' : (col.align === 'right' ? 'text-right' : 'text-left')"
                                x-html="col.html ? (row[col.key] ?? '') : String(row[col.key] ?? '')"></td>
                        </template>
                        @if($hasActions)
                            <td class="border border-gray-200 px-5 py-4 text-center dark:border-gray-800 dark:border-gray-800">
                                <div class="flex items-center justify-center gap-1">
                                    {{ $actions }}
                                </div>
                            </td>
                        @endif
                    </tr>
                </template>
                <tr x-show="!loading && recordsFiltered === 0">
                    <td :colspan="({{ $selectable ? '1' : '0' }}) + ({{ $showNumber ? '1' : '0' }}) + columns.length + {{ $hasActions ? '1' : '0' }}" class="border border-gray-200 px-6 py-12 text-center text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        {{ $emptyMessage }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 text-sm dark:border-gray-800 sm:flex-row">
        <div class="text-gray-500 dark:text-gray-400">
            Showing <span x-text="from"></span> to <span x-text="to"></span> of <span x-text="recordsFiltered"></span> entries
        </div>
        <div class="flex items-center gap-1">
            <button type="button" @click="prev()" :disabled="page <= 1"
                class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200/80 bg-white px-3 text-xs font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                Previous
            </button>
            <template x-for="p in pageList()" :key="p">
                <button type="button" x-data @click="goTo(p)" x-text="p"
                    :class="p === page ? 'bg-brand-500 text-white shadow-theme-xs' : (p === '...' ? 'cursor-default border-gray-100 text-gray-400' : 'border border-gray-200/80 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800')"
                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-xs font-semibold">
                </button>
            </template>
            <button type="button" @click="next()" :disabled="page >= totalPages"
                class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200/80 bg-white px-3 text-xs font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                Next
            </button>
        </div>
    </div>
</div>