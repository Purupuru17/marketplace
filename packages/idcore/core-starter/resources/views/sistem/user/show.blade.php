@extends('idcore::layouts.backend')
@section('title', 'Detail User - ' . $user->name)

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail User</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'User', 'url' => route('sistem.user.index')], ['label' => $user->name]]" />
    </div>
    <div class="flex items-center gap-2">
        @can('user.edit')
            <x-idcore::button variant="primary" :href="route('sistem.user.edit', $user->id)">Edit User</x-idcore::button>
        @endcan
    </div>
</div>

<div class="grid gap-6 md:grid-cols-3">
    <div class="md:col-span-1">
        <x-idcore::card title="Profil" :padding="false">
            <div class="flex flex-col items-center p-6 text-center">
                <x-idcore::avatar :name="$user->name" size="lg" />
                <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    @forelse($user->roles as $role)
                        <x-idcore::badge variant="indigo">{{ $role->name }}</x-idcore::badge>
                    @empty
                        <x-idcore::badge>Tanpa role</x-idcore::badge>
                    @endforelse
                </div>
            </div>
        </x-idcore::card>
    </div>

    <div class="md:col-span-2">
        <x-idcore::card title="Informasi Akun">
            <dl class="divide-y divide-gray-100 dark:divide-gray-800">
                <div class="flex justify-between py-3">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</dt>
                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->name }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->email }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Default Role</dt>
                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $user->roles->firstWhere('id', $user->default_role_id)?->name ?? 'Tidak ada' }}
                    </dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Bergabung</dt>
                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->created_at->format('d M Y, H:i') }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Terakhir diperbarui</dt>
                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->updated_at->format('d M Y, H:i') }}</dd>
                </div>
            </dl>
        </x-idcore::card>
    </div>
</div>
@endsection
