@props([
    'url' => null,
    'columns' => [],
    'perPage' => 10,
    'perPageOptions' => [5, 10, 25, 50, 100],
    'searchable' => true,
    'showNumber' => false,
    'emptyMessage' => 'Belum ada data.',
    'method' => 'GET',
    'actionsHeader' => 'Aksi',
    'embedded' => false,
])

@php
    $columns = collect($columns)->map(fn($col) => array_merge([
        'key' => null,
        'label' => '',
        'sortable' => true,
        'searchable' => true,
        'html' => false,
        'align' => 'left',
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

        return params;
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
    doSearch() { this.page = 1; this.fetchData(); },
    changePerPage() { this.page = 1; this.fetchData(); },
}"
class="overflow-hidden {{ $embedded ? '' : 'rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]' }}">

    @if($searchable)
    <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span>Show</span>
            <x-idcore::select name="dt-per-page" x-model="perPage" @change="changePerPage()" :options="collect($perPageOptions)->mapWithKeys(fn($o) => [$o => $o])->all()" placeholder="" class="!w-20" />
            <span>entries</span>
        </div>
        <div class="w-full md:max-w-xs">
            <x-idcore::input x-model.debounce.300ms="search" @change="doSearch()" type="search" name="dt-search" icon="magnifying-glass" placeholder="Search..." class="pr-8" />
        </div>
    </div>
    @endif

    <div class="relative max-w-full overflow-x-auto">
        <div x-show="loading" x-cloak class="absolute inset-0 z-10 flex items-center justify-center bg-white/60 dark:bg-gray-950/60">
            <svg class="h-6 w-6 animate-spin text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        <table {{ $attributes->merge(['class' => 'min-w-full']) }}>
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    @if($showNumber)
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">No</th>
                    @endif
                    @foreach($columns as $col)
                        <th @if($col['width']) style="width: {{ $col['width'] }}" @endif
                            class="px-5 py-3 {{ $col['align'] === 'center' ? 'text-center' : ($col['align'] === 'right' ? 'text-right' : 'text-left') }} text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                            @if($col['sortable'])
                                <button type="button" @click="setSort({{ Js::from($col) }})"
                                     class="group inline-flex w-full items-center gap-1.5 transition hover:text-brand-600 dark:hover:text-brand-400">
                                     <span>{{ $col['label'] }}</span>
                                     <span class="inline-flex flex-col text-gray-300 group-hover:text-brand-500 dark:text-gray-600">
                                         <svg x-show="sortKey !== {{ Js::from($col['key']) }} || sortDir !== 'desc'" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 9 4-4 4 4"></path></svg>
                                         <svg x-show="sortKey !== {{ Js::from($col['key']) }} || sortDir !== 'asc'" class="-mt-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4"></path></svg>
                                     </span>
                                </button>
                            @else
                                <span>{{ $col['label'] }}</span>
                            @endif
                        </th>
                    @endforeach
                    @if($hasActions)
                        <th class="px-5 py-3 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ $actionsHeader }}</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <template x-for="(row, idx) in rows" :key="draw + '-' + idx">
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                        @if($showNumber)
                            <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400" x-text="from + idx"></td>
                        @endif
                        <template x-for="col in columns" :key="col.key">
                            <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300"
                                :class="col.align === 'center' ? 'text-center' : (col.align === 'right' ? 'text-right' : 'text-left')"
                                x-html="col.html ? (row[col.key] ?? '') : String(row[col.key] ?? '')"></td>
                        </template>
                        @if($hasActions)
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    {{ $actions }}
                                </div>
                            </td>
                        @endif
                    </tr>
                </template>
                <tr x-show="!loading && recordsFiltered === 0">
                    <td :colspan="(showNumber ? 1 : 0) + columns.length + {{ $hasActions ? '1' : '0' }}" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">{{ $emptyMessage }}</td>
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