@extends('customer.layouts.app')
@section('title', $address ? 'Edit Alamat' : 'Tambah Alamat')

@php
    $currentLabel = old('label', $address?->label);
    $curNodeId = old('location_node_id', $address?->location_node_id);
    $curNodeName = $nodeOptions[$curNodeId] ?? $address?->locationNode?->name;
    $nodes = collect($nodeOptions)->map(fn ($name, $id) => ['id' => $id, 'name' => $name])->values()->toArray();
@endphp

@section('content')
<div x-data="{
    label: @js(in_array($currentLabel, ['Rumah', 'Kantor', 'Lainnya']) ? $currentLabel : ($currentLabel ?: 'Rumah')),
    labelOther: @js(in_array($currentLabel, ['Rumah', 'Kantor', 'Lainnya']) ? null : $currentLabel),
    nodeSheet: false,
    query: '',
    selectedNode: @js($curNodeId),
    selectedNodeName: @js($curNodeName),
    nodes: @js($nodes),
    get filtered() {
        const q = this.query.toLowerCase();
        return q ? this.nodes.filter(n => n.name.toLowerCase().includes(q)) : this.nodes;
    },
    pick(n) {
        this.selectedNode = n.id;
        this.selectedNodeName = n.name;
        this.nodeSheet = false;
    },
    setLabel(v) {
        this.label = v;
        this.labelOther = null;
    },
}" class="pb-24">

    <header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-3">
        <a href="{{ route('customer.address.index') }}" class="w-8 h-8 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-bold text-[15px] text-gray-900">{{ $address ? 'Edit Alamat' : 'Tambah Alamat' }}</h1>
    </header>

    <form method="POST"
          action="{{ $address ? route('customer.address.update', $address->id) : route('customer.address.store') }}">
        @csrf
        @if($address) @method('PUT') @endif

        <section class="px-4 pt-3.5">
            <label class="text-[11px] font-medium text-gray-500">Titik Acuan Terdekat</label>
            <button type="button" @click="nodeSheet = true"
                    class="w-full mt-1.5 flex items-center justify-between border border-gray-200 rounded-lg px-3 py-2.5">
                <span class="flex items-center gap-2 min-w-0">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="text-sm font-semibold text-gray-800 truncate" x-text="selectedNodeName || 'Pilih titik acuan'"></span>
                </span>
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <input type="hidden" name="location_node_id" :value="selectedNode">
            <p class="text-[11px] text-gray-500 mt-1.5 leading-relaxed">Jarak & ongkir ke tiap toko dihitung dari titik acuan ini, bukan lokasi GPS-mu — pilih node yang paling dekat dengan alamatmu.</p>
            @error('location_node_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </section>

        <main class="px-4 mt-3.5 space-y-3.5">
            <div>
                <label class="text-[11px] font-medium text-gray-500">Label Alamat</label>
                <div class="flex gap-2 mt-1.5">
                    @foreach(['Rumah', 'Kantor', 'Lainnya'] as $opt)
                        <button type="button" @click="setLabel('{{ $opt }}')"
                                :class="label === '{{ $opt }}' ? 'bg-emerald-700 text-white' : 'bg-white border border-gray-200 text-gray-700'"
                                class="text-xs font-semibold rounded-full px-3.5 py-2">{{ $opt }}</button>
                    @endforeach
                </div>
                <div x-show="labelOther" x-cloak class="mt-1.5">
                    <input x-model="labelOther" type="text" placeholder="Label lain"
                           class="w-full text-sm text-gray-800 border border-gray-200 rounded-lg px-3 py-2.5">
                </div>
                <input type="hidden" name="label" :value="labelOther || label">
                @error('label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="recipient_name" class="text-[11px] font-medium text-gray-500">Nama Penerima</label>
                <input id="recipient_name" type="text" name="recipient_name" value="{{ old('recipient_name', $address?->recipient_name) }}" required
                       class="w-full mt-1 text-sm text-gray-800 border border-gray-200 rounded-lg px-3 py-2.5 focus:border-emerald-600 focus:ring-0">
                @error('recipient_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="text-[11px] font-medium text-gray-500">Nomor HP Penerima</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $address?->phone) }}" required
                       class="w-full mt-1 text-sm text-gray-800 border border-gray-200 rounded-lg px-3 py-2.5 focus:border-emerald-600 focus:ring-0">
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="full_address" class="text-[11px] font-medium text-gray-500">Alamat Lengkap</label>
                <textarea id="full_address" name="full_address" rows="3" required
                          class="w-full mt-1 text-sm text-gray-800 border border-gray-200 rounded-lg px-3 py-2.5 resize-none focus:border-emerald-600 focus:ring-0">{{ old('full_address', $address?->full_address) }}</textarea>
                @error('full_address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-[13px] text-gray-700 pt-1">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address?->is_default ?? false))
                       class="w-4 h-4 rounded border-gray-300 text-emerald-700 focus:ring-emerald-600">
                Jadikan sebagai alamat utama
            </label>
        </main>

        <div class="fixed bottom-14 inset-x-0 max-w-[420px] mx-auto bg-white border-t border-gray-100 px-4 py-3 z-30">
            <button type="submit" class="w-full text-sm font-semibold text-white bg-emerald-700 rounded-lg py-3">Simpan Alamat</button>
        </div>
    </form>

    <div x-cloak x-show="nodeSheet" class="fixed inset-0 z-50" @keydown.escape.window="nodeSheet = false">
        <div class="absolute inset-0 bg-black/40" @click="nodeSheet = false"></div>
        <div class="absolute inset-x-0 bottom-0 max-w-[420px] mx-auto bg-white rounded-t-2xl max-h-[80%] flex flex-col"
             x-show="nodeSheet"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">
            <div class="flex items-center justify-between px-4 pt-4 pb-3 border-b border-gray-100 shrink-0">
                <h2 class="font-bold text-[15px] text-gray-900">Pilih Titik Acuan</h2>
                <button type="button" @click="nodeSheet = false" class="w-7 h-7 flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-4 pt-3 shrink-0">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 10-10.6 0 7.5 7.5 0 0010.6 0z"/></svg>
                    <input x-model="query" placeholder="Cari nama titik acuan..."
                           class="w-full bg-gray-100 border-0 rounded-full pl-9 pr-3 py-2.5 text-sm text-gray-800 focus:ring-0">
                </div>
                <p class="text-[11px] text-gray-500 mt-2 leading-relaxed">Pilih titik yang paling dekat dengan lokasi tinggalmu. Belum menemukan titik yang cocok? Hubungi Pusat Bantuan untuk menambahkan titik baru.</p>
            </div>

            <div class="overflow-y-auto py-2">
                <template x-for="n in filtered" :key="n.id">
                    <button type="button" @click="pick(n)"
                            :class="selectedNode === n.id ? 'bg-emerald-50' : ''"
                            class="w-full flex items-center gap-3 px-4 py-3">
                        <svg class="w-4 h-4 shrink-0" :class="selectedNode === n.id ? 'text-emerald-700' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="flex-1 text-left text-[13px]" :class="selectedNode === n.id ? 'font-semibold text-emerald-800' : 'text-gray-700'" x-text="n.name"></span>
                        <svg x-show="selectedNode === n.id" class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </template>
                <p x-show="filtered.length === 0" class="text-center text-xs text-gray-400 py-6">Tidak ada titik acuan ditemukan</p>
            </div>
        </div>
    </div>
</div>
@endsection