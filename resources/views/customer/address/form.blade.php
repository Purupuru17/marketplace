@extends('customer.layouts.app')
@section('title', $address ? 'Edit Alamat' : 'Tambah Alamat')

@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('customer.address.index') }}"
       class="mb-6 inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
        @svg('heroicon-o-arrow-left', 'h-4 w-4') Kembali
    </a>

    <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $address ? 'Edit Alamat' : 'Tambah Alamat' }}</h1>

        <form method="POST"
              action="{{ $address ? route('customer.address.update', $address->id) : route('customer.address.store') }}"
              class="mt-6 space-y-4">
            @csrf
            @if($address) @method('PUT') @endif

            <div>
                <label for="recipient_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Penerima</label>
                <input id="recipient_name" type="text" name="recipient_name"
                       value="{{ old('recipient_name', $address?->recipient_name) }}" required
                       class="mt-1 w-full rounded-lg border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                @error('recipient_name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. HP</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $address?->phone) }}" required
                       class="mt-1 w-full rounded-lg border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Label <span class="text-gray-400">(opsional)</span></label>
                <input id="label" type="text" name="label" value="{{ old('label', $address?->label) }}" placeholder="Mis. Rumah, Kantor"
                       class="mt-1 w-full rounded-lg border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>

            <div>
                <label for="location_node_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wilayah / Node Lokasi</label>
                <select id="location_node_id" name="location_node_id"
                        class="mt-1 w-full rounded-lg border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">-- Pilih wilayah --</option>
                    @foreach($nodeOptions as $id => $name)
                        <option value="{{ $id }}" @selected(old('location_node_id', $address?->location_node_id) == $id)>{{ $name }}</option>
                    @endforeach
                </select>
                @error('location_node_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="full_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Lengkap</label>
                <textarea id="full_address" name="full_address" rows="3" required
                          class="mt-1 w-full rounded-lg border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('full_address', $address?->full_address) }}</textarea>
                @error('full_address')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" name="is_default" value="1"
                       @checked(old('is_default', $address?->is_default ?? false))
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800">
                <span class="ml-2">Jadikan alamat utama</span>
            </label>

            <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ $address ? 'Simpan Perubahan' : 'Simpan Alamat' }}
            </button>
        </form>
    </div>
</div>
@endsection
