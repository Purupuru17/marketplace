@extends('idcore::layouts.backend')
@section('title', 'Beranda')

@section('content')
@php
    $user = auth()->user();
    $activeRole = \IdCore\CoreStarter\Support\ActiveRole::get($user);
    $stats = [
        ['label' => 'Users', 'value' => '3,782', 'icon' => 'users', 'change' => '11.01%', 'tone' => 'success'],
        ['label' => 'Roles', 'value' => '5,359', 'icon' => 'shield-check', 'change' => '9.05%', 'tone' => 'danger'],
    ];
    $bars = [42, 96, 50, 74, 46, 49, 72, 26, 53, 97, 70, 27];
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
@endphp

<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dashboard</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">Selamat datang, {{ $user?->name ?? 'Admin' }}</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Role aktif: <span class="font-semibold text-brand-600 dark:text-brand-400">{{ $activeRole?->name ?? 'Belum ada role' }}</span></p>
        </div>
        <div class="flex items-center gap-2">
            <x-idcore::button variant="light" size="sm">
                @svg('heroicon-o-calendar', 'h-4 w-4') Jul 5 - Jul 11
            </x-idcore::button>
            <x-idcore::button variant="primary" size="sm">
                @svg('heroicon-o-arrow-down-tray', 'h-4 w-4') Export
            </x-idcore::button>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(360px,0.7fr)]">
        <div class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                @foreach($stats as $stat)
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-start justify-between">
                            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                @svg('heroicon-o-' . $stat['icon'], 'h-6 w-6')
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold {{ $stat['tone'] === 'success' ? 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-500' : 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-500' }}">
                                @if($stat['tone'] === 'success')
                                    @svg('heroicon-o-arrow-trending-up', 'h-3.5 w-3.5')
                                @else
                                    @svg('heroicon-o-arrow-trending-down', 'h-3.5 w-3.5')
                                @endif
                                {{ $stat['change'] }}
                            </span>
                        </div>
                        <div class="mt-7">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $stat['value'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Monthly Sales</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan performa bulanan</p>
                    </div>
                    <x-idcore::button size="sm" variant="ghost" class="!h-9 !w-9 !p-0">
                        @svg('heroicon-o-ellipsis-vertical', 'h-5 w-5')
                    </x-idcore::button>
                </div>

                <div class="flex h-64 items-end gap-3 border-b border-gray-100 px-2 pb-8 dark:border-gray-800 sm:gap-5">
                    @foreach($bars as $index => $height)
                        <div class="flex flex-1 flex-col items-center justify-end gap-3">
                            <div class="w-full max-w-8 rounded-t-md bg-brand-500 transition hover:bg-brand-600" style="height: {{ $height }}%;"></div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $months[$index] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="p-6 text-center">
                <h2 class="text-left text-lg font-bold text-gray-900 dark:text-white">Monthly Target</h2>
                <p class="mt-1 text-left text-sm text-gray-500 dark:text-gray-400">Target yang ditetapkan bulan ini</p>

                <div class="relative mx-auto mt-8 flex h-56 w-56 items-center justify-center rounded-full" style="background: conic-gradient(#465fff 0 272deg, #eef2ff 272deg 360deg);">
                    <div class="flex h-40 w-40 flex-col items-center justify-center rounded-full bg-white dark:bg-gray-900">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white">75.55%</span>
                        <span class="mt-3 inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-600 dark:bg-success-500/10 dark:text-success-500">
                            @svg('heroicon-o-arrow-trending-up', 'h-3.5 w-3.5') 10%
                        </span>
                    </div>
                </div>

                <p class="mx-auto mt-4 max-w-sm text-sm leading-6 text-gray-500 dark:text-gray-400">Anda menghasilkan $3287 hari ini, lebih tinggi dari bulan lalu.</p>
            </div>

            <div class="grid grid-cols-3 border-t border-gray-100 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-950/50">
                @foreach([['Target', '$20K', 'down'], ['Revenue', '$20K', 'up'], ['Today', '$20K', 'up']] as $item)
                    <div class="px-4 py-5 text-center">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $item[0] }}</p>
                        <p class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                            {{ $item[1] }}
                            @if($item[2] === 'up')
                                @svg('heroicon-o-arrow-trending-up', 'h-4 w-4 text-success-600 inline')
                            @else
                                @svg('heroicon-o-arrow-trending-down', 'h-4 w-4 text-error-600 inline')
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Statistics</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Target yang ditetapkan untuk setiap bulan</p>
                </div>
                <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
                    <button class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm dark:bg-gray-900 dark:text-white">Overview</button>
                    <button class="px-4 py-2 text-sm font-semibold text-gray-500">Sales</button>
                    <button class="px-4 py-2 text-sm font-semibold text-gray-500">Revenue</button>
                </div>
            </div>
            <div class="mt-8 h-56 rounded-xl border border-dashed border-gray-200 bg-gradient-to-b from-brand-50 to-white p-5 dark:border-gray-800 dark:from-brand-900/20 dark:to-gray-900">
                <div class="flex h-full items-end gap-2">
                    @foreach([32, 44, 38, 58, 50, 63, 59, 72, 67, 82, 79, 76] as $height)
                        <div class="flex-1 rounded-t-md bg-brand-500/25" style="height: {{ $height }}%;"></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Aktivitas Terbaru</h2>
            <div class="mt-6 space-y-5">
                @php $activities = [['heroicon-o-user-plus', 'User baru ditambahkan', '2 menit lalu'], ['heroicon-o-shield-check', 'Permission role diperbarui', '18 menit lalu'], ['heroicon-o-list-bullet', 'Menu admin disusun ulang', '1 jam lalu']]; @endphp
                @foreach($activities as $activity)
                    <div class="flex gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            @svg($activity[0], 'h-5 w-5')
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $activity[1] }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $activity[2] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ============================================================================= --}}
{{-- COMPONENT REFERENCE — Style Guide                                            --}}
{{-- ============================================================================= --}}
<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="mb-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Component Reference</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Preview semua komponen reusable beserta varian dan props-nya.</p>
    </div>

    {{-- TOC --}}
    <div class="mb-6 flex flex-wrap gap-x-4 gap-y-1 border-b border-gray-100 pb-4 text-sm dark:border-gray-800">
        <span class="font-semibold text-gray-700 dark:text-gray-300">Daftar Isi:</span>
        <a href="#ref-button" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Button</a>
        <a href="#ref-badge" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Badge</a>
        <a href="#ref-card" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Card</a>
        <a href="#ref-input" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Input</a>
        <a href="#ref-select" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Select</a>
        <a href="#ref-textarea" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Textarea</a>
        <a href="#ref-checkbox" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Checkbox</a>
        <a href="#ref-radio" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Radio</a>
        <a href="#ref-toggle" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Toggle</a>
        <a href="#ref-alert" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Alert</a>
        <a href="#ref-avatar" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Avatar</a>
        <a href="#ref-breadcrumb" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Breadcrumb</a>
        <a href="#ref-pagination" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Pagination</a>
        <a href="#ref-table" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Table</a>
        <a href="#ref-modal" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Modal</a>
        <a href="#ref-tabs" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Tabs</a>
        <a href="#ref-file-input" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">File Input</a>
        <a href="#ref-toast" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Toast</a>
        <a href="#ref-datatable" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">DataTables</a>
        <a href="#ref-form-section" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Form Section</a>
        <a href="#ref-misc" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Komponen Baru</a>
        <a href="#ref-alpine" class="text-brand-600 hover:text-brand-800 dark:text-brand-400">Alpine Magic</a>
    </div>

    {{-- ===== BUTTON ===== --}}
    <section id="ref-button" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Button</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Komponen untuk tombol aksi. Bisa berupa <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">&lt;button&gt;</code> atau <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">&lt;a&gt;</code> (via prop <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">href</code>).</p>

        <p class="mb-1 text-xs font-semibold text-gray-600 dark:text-gray-400">Variant</p>
        <div class="mb-3 flex flex-wrap gap-2">
            <x-idcore::button variant="primary">Primary</x-idcore::button>
            <x-idcore::button variant="secondary">Secondary</x-idcore::button>
            <x-idcore::button variant="danger">Danger</x-idcore::button>
            <x-idcore::button variant="success">Success</x-idcore::button>
            <x-idcore::button variant="warning">Warning</x-idcore::button>
            <x-idcore::button variant="light">Light</x-idcore::button>
            <x-idcore::button variant="dark">Dark</x-idcore::button>
            <x-idcore::button variant="outline">Outline</x-idcore::button>
            <x-idcore::button variant="outline-danger">Outline Danger</x-idcore::button>
            <x-idcore::button variant="outline-warning">Outline Warning</x-idcore::button>
            <x-idcore::button variant="outline-success">Outline Success</x-idcore::button>
            <x-idcore::button variant="ghost">Ghost</x-idcore::button>
        </div>

        <p class="mb-1 text-xs font-semibold text-gray-600 dark:text-gray-400">Size</p>
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <x-idcore::button size="xs">XS</x-idcore::button>
            <x-idcore::button size="sm">SM</x-idcore::button>
            <x-idcore::button size="md">MD</x-idcore::button>
            <x-idcore::button size="lg">LG</x-idcore::button>
        </div>

        <p class="mb-1 text-xs font-semibold text-gray-600 dark:text-gray-400">Circle, Pill &amp; Tooltip</p>
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <x-idcore::button variant="outline-warning" size="xs" circle tooltip="Edit">@svg('heroicon-o-pencil-square', 'h-3.5 w-3.5')</x-idcore::button>
            <x-idcore::button variant="outline-danger" size="xs" circle tooltip="Hapus">@svg('heroicon-o-trash', 'h-3.5 w-3.5')</x-idcore::button>
            <x-idcore::button variant="outline-success" size="xs" circle tooltip="Lihat">@svg('heroicon-o-eye', 'h-3.5 w-3.5')</x-idcore::button>
            <x-idcore::button variant="success" pill>Pill</x-idcore::button>
            <x-idcore::button variant="outline" pill>Outline Pill</x-idcore::button>
        </div>

        <p class="mb-1 text-xs font-semibold text-gray-600 dark:text-gray-400">Block &amp; Loading</p>
        <div class="mb-4 flex flex-wrap gap-2">
            <x-idcore::button block variant="primary">Block Full</x-idcore::button>
            <x-idcore::button loading variant="primary">Loading</x-idcore::button>
        </div>

        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::button variant="primary" size="md" :href="route('...')"&gt;Label&lt;/x-idcore::button&gt;
&lt;x-idcore::button variant="outline-warning" size="xs" circle tooltip="Edit"&gt;@@svg('heroicon-o-pencil-square', 'h-3.5 w-3.5')&lt;/x-idcore::button&gt;
&lt;x-idcore::button variant="outline-danger" size="xs" circle tooltip="Hapus"&gt;@@svg('heroicon-o-trash', 'h-3.5 w-3.5')&lt;/x-idcore::button&gt;
&lt;x-idcore::button variant="success" loading block&gt;Simpan&lt;/x-idcore::button&gt;</code></pre>
    </section>

    {{-- ===== BADGE ===== --}}
    <section id="ref-badge" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Badge</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Label / status kecil. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">variant</code> (gray, success, danger, warning, info, indigo), <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">pill</code>.</p>
        <div class="mb-3 flex flex-wrap gap-2">
            <x-idcore::badge>Gray</x-idcore::badge>
            <x-idcore::badge variant="success">Success</x-idcore::badge>
            <x-idcore::badge variant="danger">Danger</x-idcore::badge>
            <x-idcore::badge variant="warning">Warning</x-idcore::badge>
            <x-idcore::badge variant="info">Info</x-idcore::badge>
            <x-idcore::badge variant="indigo">Indigo</x-idcore::badge>
            <x-idcore::badge variant="success" :pill="false">Not Pill</x-idcore::badge>
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::badge variant="success"&gt;Aktif&lt;/x-idcore::badge&gt;</code></pre>
    </section>

    {{-- ===== CARD ===== --}}
    <section id="ref-card" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Card</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Container dengan border, shadow, dan opsional header + footer. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">title</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">subtitle</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">padding</code>. Slot: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">actions</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">footer</code>.</p>
        <div class="mb-3 grid gap-3 md:grid-cols-2">
            <x-idcore::card title="Judul Card" subtitle="Ini subtitle card">
                <p class="text-sm text-gray-600 dark:text-gray-300">Konten body card di sini.</p>
            </x-idcore::card>
            <x-idcore::card title="Dengan Actions" subtitle="Ada tombol di header">
                <x-slot:actions>
                    <x-idcore::button size="xs" variant="outline">Action</x-idcore::button>
                </x-slot:actions>
                <p class="text-sm text-gray-600 dark:text-gray-300">Body card.</p>
                <x-slot:footer>
                    <p class="text-xs text-gray-400">Footer card</p>
                </x-slot:footer>
            </x-idcore::card>
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::card title="Judul" subtitle="Subtitle" class="max-w-xl"&gt;
    &lt;x-slot:actions&gt;
        &lt;x-idcore::button size="xs" variant="outline"&gt;Edit&lt;/x-idcore::button&gt;
    &lt;/x-slot:actions&gt;
    Konten body
    &lt;x-slot:footer&gt;Footer&lt;/x-slot:footer&gt;
&lt;/x-idcore::card&gt;

&lt;x-idcore::card :padding="false"&gt;Tanpa padding&lt;/x-idcore::card&gt;</code></pre>
    </section>

    {{-- ===== INPUT ===== --}}
    <section id="ref-input" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Input</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Field input teks. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">name</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">label</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">type</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">value</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">placeholder</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">required</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">hint</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">icon</code>.</p>
        <div class="mb-3 grid gap-3 md:grid-cols-2">
            <x-idcore::input name="ref-nama" label="Nama" placeholder="Masukkan nama" />
            <x-idcore::input name="ref-email" type="email" label="Email" value="user@example.com" hint="Email aktif" />
            <x-idcore::input name="ref-pass" type="password" label="Password" required placeholder="Min 8 karakter" />
            <x-idcore::input class="border-error-500 focus:border-error-500" name="ref-error" label="Dengan Error" value="salah" />
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::input name="nama" label="Nama" placeholder="..." /&gt;
&lt;x-idcore::input name="email" type="email" label="Email" value="@{{ $email }}" required /&gt;</code></pre>
    </section>

    {{-- ===== SELECT ===== --}}
    <section id="ref-select" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Select</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Dropdown pilihan. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">name</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">label</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">options</code> (array), <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">selected</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">placeholder</code>.</p>
        <div class="mb-3 grid gap-3 md:grid-cols-3">
            <x-idcore::select name="ref-select1" label="Pilih Role" :options="['admin' => 'Admin', 'user' => 'User', 'editor' => 'Editor']" />
            <x-idcore::select name="ref-select2" label="Dengan selected" :options="['10' => '10', '25' => '25', '50' => '50']" selected="25" />
            <x-idcore::select name="ref-select3" />
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::select name="role" label="Role" :options="['admin' => 'Admin', 'user' => 'User']" :selected="$default" /&gt;</code></pre>
    </section>

    {{-- ===== TEXTAREA ===== --}}
    <section id="ref-textarea" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Textarea</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Field teks multi-baris. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">name</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">label</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">value</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">rows</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">hint</code>.</p>
        <div class="mb-3 max-w-lg">
            <x-idcore::textarea name="ref-textarea" label="Keterangan" rows="3" placeholder="Tulis keterangan..." hint="Maksimal 500 karakter" />
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::textarea name="keterangan" label="Keterangan" rows="3" /&gt;</code></pre>
    </section>

    {{-- ===== CHECKBOX / RADIO / TOGGLE ===== --}}
    <section id="ref-checkbox" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Checkbox</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">name</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">value</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">label</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">checked</code>.</p>
        <div class="mb-4 flex flex-wrap gap-4">
            <x-idcore::checkbox name="ref-cb1" value="1" label="Unchecked" />
            <x-idcore::checkbox name="ref-cb2" value="1" label="Checked" checked />
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::checkbox name="roles[]" :value="$role-&gt;name" label="Admin" :checked="$user-&gt;hasRole('admin')" /&gt;</code></pre>
    </section>

    <section id="ref-radio" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Radio</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">name</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">value</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">label</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">selected</code>.</p>
        <div class="mb-4 flex flex-wrap gap-4">
            <x-idcore::radio name="ref-radio" value="option-a" label="Opsi A" selected="option-a" />
            <x-idcore::radio name="ref-radio" value="option-b" label="Opsi B" />
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::radio name="default_role" value="admin" label="Default" :selected="$defaultRole" /&gt;</code></pre>
    </section>

    <section id="ref-toggle" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Toggle</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Saklar on/off. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">name</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">label</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">checked</code>.</p>
        <div class="mb-4 flex flex-wrap gap-4">
            <x-idcore::toggle name="ref-tog1" label="Toggle Nonaktif" />
            <x-idcore::toggle name="ref-tog2" label="Toggle Aktif" checked />
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::toggle name="is_active" label="Aktif" :checked="$menu-&gt;is_active ?? true" /&gt;</code></pre>
    </section>

    {{-- ===== ALERT ===== --}}
    <section id="ref-alert" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Alert</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Notifikasi dalam halaman. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">variant</code> (info, success, error, warning), <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">dismissible</code>.</p>
        <div class="mb-3 space-y-2">
            <x-idcore::alert variant="info">Informasi: Data berhasil dimuat.</x-idcore::alert>
            <x-idcore::alert variant="success">Sukses: Data berhasil disimpan.</x-idcore::alert>
            <x-idcore::alert variant="error">Error: Gagal menyimpan data.</x-idcore::alert>
            <x-idcore::alert variant="warning">Peringatan: Data akan dihapus permanen.</x-idcore::alert>
            <x-idcore::alert variant="success" :dismissible="false">Alert tanpa tombol tutup.</x-idcore::alert>
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::alert variant="success"&gt;Pesan sukses&lt;/x-idcore::alert&gt;
&lt;x-idcore::alert variant="danger" :dismissible="false"&gt;Tanpa tombol tutup&lt;/x-idcore::alert&gt;</code></pre>
    </section>

    {{-- ===== AVATAR ===== --}}
    <section id="ref-avatar" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Avatar</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Lingkaran inisial nama. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">name</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">size</code> (sm, md, lg).</p>
        <div class="mb-3 flex flex-wrap items-center gap-3">
            <x-idcore::avatar name="Admin" size="sm" />
            <x-idcore::avatar name="User" size="md" />
            <x-idcore::avatar name="Super Admin" size="lg" />
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::avatar name="@{{ $user-&gt;name }}" size="sm" /&gt;</code></pre>
    </section>

    {{-- ===== BREADCRUMB ===== --}}
    <section id="ref-breadcrumb" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Breadcrumb</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Navigasi hirarki. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">items</code> (array of <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">['label', 'url']</code>).</p>
        <div class="mb-3">
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::breadcrumb :items="[['label' =&gt; 'Home', 'url' =&gt; route('dashboard')], ['label' =&gt; 'User']]" /&gt;</code></pre>
    </section>

    {{-- ===== PAGINATION ===== --}}
    <section id="ref-pagination" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Pagination</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Navigasi halaman untuk DataTable. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">paginator</code> (instance <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">LengthAwarePaginator</code>).</p>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::pagination :paginator="$users" /&gt;</code></pre>
    </section>

    {{-- ===== TABLE ===== --}}
    <section id="ref-table" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Table &amp; Table Empty</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Wrapper tabel dan state kosong.</p>
        <div class="mb-3 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <x-idcore::table>
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                        <td class="px-6 py-4 text-gray-900 dark:text-white">Admin</td>
                        <td class="px-6 py-4"><x-idcore::badge variant="success">Aktif</x-idcore::badge></td>
                    </tr>
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                        <td class="px-6 py-4 text-gray-900 dark:text-white">User</td>
                        <td class="px-6 py-4"><x-idcore::badge>Nonaktif</x-idcore::badge></td>
                    </tr>
                </tbody>
            </x-idcore::table>
        </div>
        <div class="mb-3 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <x-idcore::table>
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr><th class="px-6 py-3 text-left text-xs font-semibold">Data</th></tr>
                </thead>
                <tbody>
                    <x-idcore::table-empty colspan="1" message="Belum ada data." />
                </tbody>
            </x-idcore::table>
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::table&gt;
    &lt;thead&gt;&lt;tr&gt;&lt;th&gt;Nama&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;
    &lt;tbody&gt;
        @@forelse($items as $item)
            &lt;tr&gt;&lt;td&gt;@{{ $item-&gt;name }}&lt;/td&gt;&lt;/tr&gt;
        @@empty
            &lt;x-idcore::table-empty colspan="1" message="Kosong" /&gt;
        @@endforelse
    &lt;/tbody&gt;
&lt;/x-idcore::table&gt;</code></pre>
    </section>

    {{-- ===== MODAL ===== --}}
    <section id="ref-modal" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Modal</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Dialog / popup. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">maxWidth</code> (sm, md, lg, xl, 2xl). Slot: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">trigger</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">title</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">footer</code>.</p>
        <div class="mb-3">
            <x-idcore::modal title="Konfirmasi Hapus" max-width="sm">
                <x-slot:trigger>
                    <x-idcore::button variant="danger" size="sm">Buka Modal</x-idcore::button>
                </x-slot:trigger>
                <p class="text-sm text-gray-600 dark:text-gray-300">Apakah anda yakin ingin menghapus data ini?</p>
                <x-slot:footer>
                    <x-idcore::button size="sm" variant="outline" @click="open = false">Batal</x-idcore::button>
                    <x-idcore::button size="sm" variant="danger">Ya, Hapus</x-idcore::button>
                </x-slot:footer>
            </x-idcore::modal>
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::modal title="Judul" max-width="md"&gt;
    &lt;x-slot:trigger&gt;
        &lt;x-idcore::button&gt;Buka Modal&lt;/x-idcore::button&gt;
    &lt;/x-slot:trigger&gt;
    Isi modal...
    &lt;x-slot:footer&gt;
        &lt;x-idcore::button variant="outline" @click="open = false"&gt;Batal&lt;/x-idcore::button&gt;
        &lt;x-idcore::button variant="primary"&gt;Simpan&lt;/x-idcore::button&gt;
    &lt;/x-slot:footer&gt;
&lt;/x-idcore::modal&gt;</code></pre>
    </section>

    {{-- ===== TABS ===== --}}
    <section id="ref-tabs" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Tabs</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Navigasi tab. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">default</code> (key tab aktif awal). Komponen anak: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">tab-button</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">tab-panel</code> dengan prop <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">value</code>.</p>
        <div class="mb-3">
            <x-idcore::tabs default="tab-a">
                <div class="flex flex-wrap gap-1 border-b border-gray-200 mb-4 dark:border-gray-800">
                    <x-idcore::tab-button value="tab-a">Tab A</x-idcore::tab-button>
                    <x-idcore::tab-button value="tab-b">Tab B</x-idcore::tab-button>
                    <x-idcore::tab-button value="tab-c">Tab C</x-idcore::tab-button>
                </div>
                <x-idcore::tab-panel value="tab-a">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Konten Tab A</p>
                </x-idcore::tab-panel>
                <x-idcore::tab-panel value="tab-b">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Konten Tab B</p>
                </x-idcore::tab-panel>
                <x-idcore::tab-panel value="tab-c">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Konten Tab C</p>
                </x-idcore::tab-panel>
            </x-idcore::tabs>
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::tabs default="profile"&gt;
    &lt;x-idcore::tab-button value="profile"&gt;Profile&lt;/x-idcore::tab-button&gt;
    &lt;x-idcore::tab-button value="settings"&gt;Settings&lt;/x-idcore::tab-button&gt;
    &lt;x-idcore::tab-panel value="profile"&gt;Konten profile&lt;/x-idcore::tab-panel&gt;
    &lt;x-idcore::tab-panel value="settings"&gt;Konten settings&lt;/x-idcore::tab-panel&gt;
&lt;/x-idcore::tabs&gt;</code></pre>
    </section>

    {{-- ===== FILE INPUT ===== --}}
    <section id="ref-file-input" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">File Input</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Upload file drag &amp; drop. Props: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">name</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">label</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">accept</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">hint</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">preview</code>.</p>
        <div class="mb-3 max-w-md">
            <x-idcore::file-input name="ref-file" label="Upload File" hint="Format: JPG, PNG, PDF" accept=".jpg,.jpeg,.png,.pdf" />
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::file-input name="foto" label="Upload Foto" accept=".jpg,.png" /&gt;</code></pre>
    </section>

    {{-- ===== TOAST ===== --}}
    <section id="ref-toast" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Toast</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Notifikasi pop-up via <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">$store.toast</code>. Komponen <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">x-idcore::toast</code> sudah include di layout.</p>
        <div class="mb-3 flex flex-wrap gap-2">
            <x-idcore::button size="sm" variant="success" @click="$store.toast.success('Data berhasil disimpan!')">Toast Success</x-idcore::button>
            <x-idcore::button size="sm" variant="danger" @click="$store.toast.error('Terjadi kesalahan!')">Toast Error</x-idcore::button>
            <x-idcore::button size="sm" variant="warning" @click="$store.toast.warning('Periksa kembali input anda.')">Toast Warning</x-idcore::button>
            <x-idcore::button size="sm" variant="outline" @click="$store.toast.info('Info dari sistem.')">Toast Info</x-idcore::button>
        </div>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::button @click="$store.toast.success('Pesan')"&gt;Toast&lt;/x-idcore::button&gt;
&lt;x-idcore::button @click="$store.toast.error('Error')"&gt;Error&lt;/x-idcore::button&gt;
&lt;x-idcore::button @click="$store.toast.warning('Warning')"&gt;Warning&lt;/x-idcore::button&gt;
&lt;x-idcore::button @click="$store.toast.info('Info')"&gt;Info&lt;/x-idcore::button&gt;</code></pre>
    </section>

    {{-- ===== DATATABLE ===== --}}
    <section id="ref-datatable" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">DataTables (Client-side &amp; Server-driven)</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Tabel interaktif dengan search, sort, dan pagination. Dua mode: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">datatable</code> (client-side, data penuh di browser, cocok untuk data kecil) dan <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">datatable-server</code> (server-driven via <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">DataTableService</code>, cocok untuk data besar).</p>

        <div class="mb-3 space-y-3">
            <x-idcore::card :padding="false">
                <x-slot:actions>
                    <x-idcore::badge variant="indigo">Client-side</x-idcore::badge>
                </x-slot:actions>
                <div class="flex flex-col gap-1 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">DataTables Client-side</h4>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Data dimuat penuh di browser. Search, sort, dan pagination berjalan tanpa request ulang.</p>
                </div>
                <div class="p-0">
                    <x-idcore::datatable :columns="$roleColumns" :rows="$roleRows" :per-page="10" searchable />
                </div>
            </x-idcore::card>

            <x-idcore::card :padding="false">
                <x-slot:actions>
                    <x-idcore::badge variant="brand">Server-driven</x-idcore::badge>
                </x-slot:actions>
                <div class="flex flex-col gap-1 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">DataTables Server-driven</h4>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Search, sort, dan pagination diproses server-side via <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">DataTableService</code>. Sumber data di sini: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">dashboard.roles-json</code>.</p>
                </div>
                <div class="p-0">
                    <x-idcore::datatable-server :url="$roleJsonUrl" :columns="$roleColumns" :per-page="10" searchable />
                </div>
            </x-idcore::card>
        </div>

        <p class="mb-1 text-xs font-semibold text-gray-600 dark:text-gray-400">Prop</p>
        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::datatable :columns="$columns" :rows="$rows" :per-page="10" searchable :show-number="true" /&gt;
&lt;x-idcore::datatable-server :url="route('sistem.group.ajax')" :columns="$columns" :per-page="10" searchable /&gt;

// Kolom
[
    ['key' => 'name', 'label' => 'Role', 'sortable' => true],
    ['key' => 'status', 'label' => 'Status', 'sortable' => false, 'html' => true, 'align' => 'center'],
]</code></pre>
    </section>

    {{-- ===== FORM SECTION & FIELD ===== --}}
    <section id="ref-form-section" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Form Section &amp; Field</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Layout form dengan grid kolom responsif. <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">form-section</code> menampung <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">field</code> (label + control + hint + error). Props form-section: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">title</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">subtitle</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">description</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">columns</code> (1, 2, 3). Slot: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">footer</code>.</p>

        <div class="mb-3">
            <x-idcore::form-section title="Informasi Toko" subtitle="Contoh kombinasi field dalam form-section." :columns="2">
                <x-idcore::field name="ref-nama-toko" label="Nama Toko" required>
                    <x-idcore::input name="ref-nama-toko" type="text" placeholder="Nama toko Anda" />
                </x-idcore::field>
                <x-idcore::field name="ref-email-toko" label="Email" required>
                    <x-idcore::input name="ref-email-toko" type="email" placeholder="nama@email.com" />
                </x-idcore::field>
                <x-idcore::field name="ref-deskripsi" label="Deskripsi" hint="Maksimal 200 karakter." :columns="3">
                    <x-idcore::textarea name="ref-deskripsi" rows="3" placeholder="Ceritakan tentang toko Anda" />
                </x-idcore::field>
                <x-slot:footer>
                    <div class="flex items-center justify-end gap-2">
                        <x-idcore::button variant="outline" type="button">Reset</x-idcore::button>
                        <x-idcore::button type="button" @click="$store.toast.success('Demo form: data tidak benar-benar disimpan.')">Simpan Informasi</x-idcore::button>
                    </div>
                </x-slot:footer>
            </x-idcore::form-section>
        </div>

        <pre class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs leading-relaxed dark:border-gray-700 dark:bg-gray-950"><code>&lt;x-idcore::form-section title="Informasi Toko" :columns="2"&gt;
    &lt;x-idcore::field name="nama_toko" label="Nama Toko" required&gt;
        &lt;x-idcore::input name="nama_toko" type="text" /&gt;
    &lt;/x-idcore::field&gt;
    &lt;x-slot:footer&gt;
        &lt;x-idcore::button type="submit"&gt;Simpan&lt;/x-idcore::button&gt;
    &lt;/x-slot:footer&gt;
&lt;/x-idcore::form-section&gt;</code></pre>
    </section>

    {{-- ===== KOMONEN BARU ===== --}}
    <section id="ref-misc" class="mb-8 border-b border-gray-100 pb-6 dark:border-gray-800">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Komponen Baru Lainnya</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Komponen utility baru hasil sprint Step 15.</p>

        <div class="mb-3 grid gap-3 md:grid-cols-2">
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="mb-2 text-sm font-semibold text-gray-800 dark:text-white">page-header</p>
                <x-idcore::page-header title="Contoh Header" subtitle="Subtitle halaman." :breadcrumb="[['Beranda', route('dashboard')], ['Contoh']]">
                    <x-slot:actions>
                        <x-idcore::button size="sm" variant="primary">Aksi</x-idcore::button>
                    </x-slot:actions>
                </x-idcore::page-header>
            </div>
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="mb-2 text-sm font-semibold text-gray-800 dark:text-white">metric-card &amp; progress</p>
                <x-idcore::metric-card label="Users" value="3,782" icon="users" tone="success" />
                <div class="mt-3">
                    <x-idcore::progress value="75" label="Kapasitas" />
                </div>
            </div>
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="mb-2 text-sm font-semibold text-gray-800 dark:text-white">empty-state &amp; status-badge</p>
                <x-idcore::status-badge status="completed" />
                <x-idcore::status-badge status="pending" />
                <x-idcore::status-badge status="failed" />
                <x-idcore::empty-state title="Belum ada data" description="Belum ada record yang ditambahkan." class="mt-3" />
            </div>
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="mb-2 text-sm font-semibold text-gray-800 dark:text-white">toolbar</p>
                <x-idcore::toolbar>
                    <x-slot:leading>
                        <x-idcore::input name="ref-tb-search" type="search" placeholder="Cari..." class="!w-64" />
                    </x-slot:leading>
                    <x-slot:actions>
                        <x-idcore::button size="sm" variant="primary">Tambah</x-idcore::button>
                    </x-slot:actions>
                </x-idcore::toolbar>
            </div>
        </div>
    </section>

    {{-- ===== ALPINE MAGIC ===== --}}
    <section id="ref-alpine" class="mb-2">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Alpine Magic &amp; Stores</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Utility magic dan store global yang tersedia di seluruh halaman.</p>

        <div class="mb-4 grid gap-3 md:grid-cols-2">
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="mb-1 text-sm font-semibold text-gray-800 dark:text-white">$confirm({...})</p>
                <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">Native Alpine confirm dialog. Return Promise&lt;boolean&gt;.</p>
                <pre class="overflow-x-auto rounded bg-gray-50 p-2 text-xs leading-relaxed dark:bg-gray-950"><code>$confirm({
    title: 'Hapus?',
    message: 'Yakin?',
    confirmText: 'Ya',
    variant: 'danger' // atau warning / brand
}).then(ok => { if (ok) ... });</code></pre>
            </div>
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="mb-1 text-sm font-semibold text-gray-800 dark:text-white">$store.theme</p>
                <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">Dark mode: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">theme.dark</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">theme.toggle()</code>.</p>
                <p class="mb-1 text-sm font-semibold text-gray-800 dark:text-white">$store.layout</p>
                <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">Sidebar: <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">layout.sidebarOpen</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">layout.collapsed</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">layout.toggleSidebar()</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">layout.toggleCollapse()</code>.</p>
                <p class="mb-1 text-sm font-semibold text-gray-800 dark:text-white">$store.toast</p>
                <p class="text-xs text-gray-500 dark:text-gray-400"><code class="rounded bg-gray-100 px-1 dark:bg-gray-800">toast.success()</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">.error()</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">.warning()</code>, <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">.info()</code>.</p>
            </div>
        </div>

        <div class="rounded-lg border border-warning-200 bg-warning-50 p-3 text-xs text-warning-800 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-500">
            <p class="font-semibold">Best Practice:</p>
            <ul class="mt-1 list-inside list-disc space-y-0.5">
                <li>Pakai komponen sebisa mungkin — styling, dark mode, dan responsive sudah dihandle.</li>
                <li>Gunakan <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">$confirm()</code> untuk semua konfirmasi hapus — ganti pesan &amp; title sesuai konteks.</li>
                <li>Untuk tombol aksi edit/hapus di tabel, gunakan <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">variant="outline-warning" size="xs" circle tooltip="Edit"</code> dan <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">variant="outline-danger" size="xs" circle tooltip="Hapus"</code>.</li>
                <li>Filter toolbar (search + per_page) bungkus dalam <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">&lt;form method="GET"&gt;</code> dengan <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">onchange="this.form.submit()"</code> di select.</li>
            </ul>
        </div>
    </section>
</div>

@endsection
