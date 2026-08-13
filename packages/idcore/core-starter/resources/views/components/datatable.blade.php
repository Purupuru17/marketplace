@props([
    'columns' => [],
    'rows' => [],
    'perPage' => 10,
    'perPageOptions' => [5, 10, 25, 50, 100],
    'searchable' => true,
    'showNumber' => false,
    'emptyMessage' => 'Belum ada data.',
    'actionsHeader' => 'Aksi',
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
    columns: @js($columns),
    rows: @js(array_values($rows)),
    perPage: @js((int) $perPage),
    perPageOptions: @js($perPageOptions),
    search: '',
    sortKey: null,
    sortDir: 'asc',
    page: 1,

    get filtered() {
        let rows = this.rows;
        if (this.search.trim()) {
            const q = this.search.trim().toLowerCase();
            rows = rows.filter(r => Object.values(r).some(v => String(v ?? '').toLowerCase().includes(q)));
        }
        return rows;
    },

    get sorted() {
        const rows = [...this.filtered];
        if (!this.sortKey) return rows;
        const key = this.sortKey;
        const dir = this.sortDir === 'asc' ? 1 : -1;
        return rows.sort((a, b) => {
            const av = a[key];
            const bv = b[key];
            if (av == null && bv == null) return 0;
            if (av == null) return -1 * dir;
            if (bv == null) return 1 * dir;
            if (typeof av === 'number' && typeof bv === 'number') return (av - bv) * dir;
            return String(av).localeCompare(String(bv), 'id', { numeric: true }) * dir;
        });
    },

    get paginated() {
        const start = (this.page - 1) * this.perPage;
        return this.sorted.slice(start, start + this.perPage);
    },

    get totalFiltered() { return this.filtered.length; },
    get totalPages() { return Math.max(1, Math.ceil(this.totalFiltered / this.perPage)); },
    get from() { return this.totalFiltered === 0 ? 0 : (this.page - 1) * this.perPage + 1; },
    get to() { return Math.min(this.page * this.perPage, this.totalFiltered); },

    setSort(col) {
        if (!col.sortable) return;
        if (this.sortKey === col.key) {
            this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortKey = col.key;
            this.sortDir = 'asc';
        }
        this.page = 1;
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
        }
    },
    prev() { this.page = Math.max(1, this.page - 1); },
    next() { this.page = Math.min(this.totalPages, this.page + 1); },
}"
class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]">

    @if($searchable)
    <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span>Show</span>
            <x-idcore::select name="dt-per-page" x-model="perPage" @change="page = 1" :options="collect($perPageOptions)->mapWithKeys(fn($o) => [$o => $o])->all()" placeholder="" class="!w-16" />
            <span>entries</span>
        </div>
        <div class="relative w-full md:max-w-xs">
            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">@svg('heroicon-o-magnifying-glass', 'h-4 w-4')</span>
            <x-idcore::input x-model="search" type="search" name="dt-search" placeholder="Search..." class="pr-8" />
        </div>
    </div>
    @endif

    <div class="max-w-full overflow-x-auto">
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
                                    class="inline-flex items-center gap-1 transition hover:text-gray-800 dark:hover:text-gray-200">
                                    <span>{{ $col['label'] }}</span>
                                    <svg x-show="sortKey === {{ Js::from($col['key']) }} && sortDir === 'asc'" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    <svg x-show="sortKey === {{ Js::from($col['key']) }} && sortDir === 'desc'" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
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
                <template x-for="(row, idx) in paginated" :key="idx">
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
                <tr x-show="totalFiltered === 0">
                    <td :colspan="(showNumber ? 1 : 0) + columns.length + {{ $hasActions ? '1' : '0' }}" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">{{ $emptyMessage }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 text-sm dark:border-gray-800 sm:flex-row">
        <div class="text-gray-500 dark:text-gray-400">
            Showing <span x-text="from"></span> to <span x-text="to"></span> of <span x-text="totalFiltered"></span> entries
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