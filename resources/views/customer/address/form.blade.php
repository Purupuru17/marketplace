@extends('customer.layouts.app')
@section('title', $address ? 'Edit Alamat' : 'Tambah Alamat')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-3">
    <a href="{{ route('customer.address.index') }}" class="w-8 h-8 flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="font-bold text-[15px] text-gray-900">{{ $address ? 'Edit Alamat' : 'Tambah Alamat' }}</h1>
</header>

<main class="px-4">
    <form method="POST"
          action="{{ $address ? route('customer.address.update', $address->id) : route('customer.address.store') }}"
          class="mt-4 bg-white rounded-xl border border-gray-100 p-4 space-y-4">
        @csrf
        @if($address) @method('PUT') @endif

        <div>
            <label for="recipient_name" class="block text-xs font-medium text-gray-700">Nama Penerima</label>
            <input id="recipient_name" type="text" name="recipient_name" value="{{ old('recipient_name', $address?->recipient_name) }}" required
                   class="mt-1 w-full rounded-lg border-gray-200 bg-white px-3 py-2.5 text-sm focus:ring-0 focus:border-emerald-600">
            @error('recipient_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="block text-xs font-medium text-gray-700">No. HP</label>
            <input id="phone" type="text" name="phone" value="{{ old('phone', $address?->phone) }}" required
                   class="mt-1 w-full rounded-lg border-gray-200 bg-white px-3 py-2.5 text-sm focus:ring-0 focus:border-emerald-600">
            @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="label" class="block text-xs font-medium text-gray-700">Label <span class="text-gray-400">(opsional)</span></label>
            <input id="label" type="text" name="label" value="{{ old('label', $address?->label) }}" placeholder="Mis. Rumah, Kantor"
                   class="mt-1 w-full rounded-lg border-gray-200 bg-white px-3 py-2.5 text-sm focus:ring-0 focus:border-emerald-600">
        </div>

        <div>
            <label for="location_node_id" class="block text-xs font-medium text-gray-700">Wilayah / Node Lokasi</label>
            <select id="location_node_id" name="location_node_id"
                    class="mt-1 w-full rounded-lg border-gray-200 bg-white px-3 py-2.5 text-sm focus:ring-0 focus:border-emerald-600">
                <option value="">-- Pilih wilayah --</option>
                @foreach($nodeOptions as $id => $name)
                    <option value="{{ $id }}" @selected(old('location_node_id', $address?->location_node_id) == $id)>{{ $name }}</option>
                @endforeach
            </select>
            @error('location_node_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="full_address" class="block text-xs font-medium text-gray-700">Alamat Lengkap</label>
            <textarea id="full_address" name="full_address" rows="3" required
                      class="mt-1 w-full rounded-lg border-gray-200 bg-white px-3 py-2.5 text-sm focus:ring-0 focus:border-emerald-600">{{ old('full_address', $address?->full_address) }}</textarea>
            @error('full_address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center text-[13px] text-gray-600">
            <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address?->is_default ?? false))
                   class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-600">
            <span class="ml-2">Jadikan alamat utama</span>
        </label>

        <button type="submit"
                class="w-full text-sm font-semibold text-white bg-emerald-700 rounded-lg py-3">Simpan Alamat</button>
    </form>
</main>
@endsection