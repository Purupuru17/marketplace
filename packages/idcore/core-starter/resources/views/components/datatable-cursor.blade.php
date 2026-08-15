@props([
    'url' => null,
    'columns' => [],
    'perPage' => 20,
    'perPageOptions' => [20, 50, 100],
    'searchable' => true,
    'showNumber' => false,
    'emptyMessage' => 'Belum ada data.',
    'method' => 'GET',
    'actionsHeader' => 'Aksi',
    'embedded' => false,
    'defaultSortBy' => 'id',
    'defaultSortDir' => 'desc',
])

@php
    $columns = collect($columns)->map(fn($col) => array_merge([
        'key' => null,
        'label' => '',
        'sortable' => true,
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
    sortKey: {{ Js::from($defaultSortBy) }},
    sortDir: {{ Js::from($defaultSortDir) }},
    cursor: null,
    cursorStack: [],
    nextCursor: null,
    prevCursor: null,
    hasMore: false,
    rows: @js([]),
    loading: false,
    itemOffset: 0,

    init() {
        this.fetchData();
    },

    buildQuery() {
        const params = new URLSearchParams();
        params.set('search', this.search);
        params.set('per_page', this.perPage);
        params.set('sort_by', this.sortKey);
        params.set('sort_dir', this.sortDir);
        if (this.cursor) params.set('cursor', this.cursor);
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
            this.nextCursor = json.next_cursor ?? null;
            this.prevCursor = json.prev_cursor ?? null;
            this.hasMore = json.has_more ?? false;
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
        this.resetAndFetch();
    },

    resetAndFetch() {
        // Ganti search/sort = mulai dari awal lagi, cursor lama gak valid
        this.cursor = null;
        this.cursorStack = [];
        this.fetchData();
    },

    next() {
        if (!this.hasMore) return;
        this.cursorStack.push(this.cursor);
        this.cursor = this.nextCursor;
        this.itemOffset += this.rows.length; // simpan sebelum rows di-replace fetchData()
        this.fetchData();
    },

    prev() {
        this.cursor = this.cursorStack.pop() ?? null;
        this.itemOffset = Math.max(0, this.itemOffset - this.perPage);
        this.fetchData();
    },

    doSearch() {
        const len = this.search.trim().length;
        if (len === 0 || len >= 3) {
            this.resetAndFetch();
        }
    },
    
    changePerPage() { this.resetAndFetch(); },
}"
class="overflow-hidden {{ $embedded ? '' : 'rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]' }}">

    @if($searchable)
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <span>Show</span>
                <x-idcore::select name="dtc-per-page" x-model="perPage" @change="changePerPage()" :options="collect($perPageOptions)->mapWithKeys(fn($o) => [$o => $o])->all()" placeholder="" class="!w-20" />
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
                <template x-for="(row, idx) in rows" :key="row.id ?? idx">
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                        @if($showNumber)
                            <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400" x-text="itemOffset + idx + 1"></td>
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

                <tr x-show="!loading && rows.length === 0">
                    <td :colspan="(showNumber ? 1 : 0) + columns.length + {{ $hasActions ? '1' : '0' }}" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">{{ $emptyMessage }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Footer: cuma Prev/Next, gak ada nomor halaman (gak ada COUNT total = gak ada total pages) --}}
    <div class="flex items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 text-sm dark:border-gray-800">
        <div class="text-gray-500 dark:text-gray-400">
            <span x-text="rows.length"></span> data pada halaman ini
        </div>
        <div class="flex items-center gap-1">
            <button type="button" @click="prev()" :disabled="cursorStack.length === 0 || loading"
                class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200/80 bg-white px-3 text-xs font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                Previous
            </button>
            <button type="button" @click="next()" :disabled="!hasMore || loading"
                class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200/80 bg-white px-3 text-xs font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                Next
            </button>
        </div>
    </div>
</div>