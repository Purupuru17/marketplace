<?php

namespace App\Http\Controllers\Store;

use App\Models\Store;
use App\Services\Store\StoreService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\Render;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreController extends BaseCoreController
{
    private $module = 'toko.store';

    protected $view = 'store.store';

    public function __construct(protected StoreService $service) {}

    public function index(Request $request)
    {
        $columns = [
            ['key' => 'store', 'label' => 'Toko', 'html' => true, 'align' => 'left'],
            ['key' => 'owner', 'label' => 'Pemilik', 'align' => 'left'],
            ['key' => 'level', 'label' => 'Level', 'html' => true, 'align' => 'center'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'html' => true, 'align' => 'center'],
        ];

        $compact = [
            'title' => 'Toko',
            'subtitle' => 'Data Toko',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Toko']],

            'columns' => $columns,
        ];

        return view($this->view.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData' => null,
            'userOptions' => $this->service->userOptions(),
            'levelOptions' => $this->service->levelOptions(),
            'nodeOptions' => $this->service->nodeOptions(),
            'hoursByDay' => collect(),

            'title' => 'Tambah Toko',
            'subtitle' => 'Data toko sebagai tenant marketplace',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Toko', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->view.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $this->validateStore($request);

        $this->applyUploads($request, $validated);
        $this->service->create($validated, $request->input('hours', []));

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Toko berhasil ditambahkan.');
    }

    public function edit(Store $store)
    {
        $store->load('operatingHours');

        $compact = [
            'formData' => $store,
            'userOptions' => $this->service->userOptions(),
            'levelOptions' => $this->service->levelOptions(),
            'nodeOptions' => $this->service->nodeOptions(),
            'hoursByDay' => $store->operatingHours->keyBy('day'),

            'title' => 'Edit Toko',
            'subtitle' => 'Data toko sebagai tenant marketplace',

            'action' => route($this->module.'.update', $store->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Toko', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->view.'.form', $compact);
    }

    public function update(Request $request, Store $store)
    {
        $validated = $this->validateStore($request);

        $this->applyUploads($request, $validated, $store);
        $this->service->update($store, $validated, $request->input('hours', []));

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Toko berhasil diperbarui.');
    }

    public function destroy(Store $store)
    {
        $this->service->delete($store);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Toko berhasil dihapus.');
    }

    public function ajax(Request $request)
    {
        $type = $request->input('type');
        $source = $request->input('source');

        return match ($type) {
            'table' => match ($source) {
                'index' => $this->tableIndex($request),
                default => response()->json(['status' => 'error', 'message' => 'Sumber data tidak valid.'], 400),
            },
            default => response()->json(['status' => 'error', 'message' => 'Aksi tidak valid.'], 400),
        };
    }

    private function tableIndex(Request $request)
    {
        return DataTableService::process(
            $request,
            Store::with(['owner', 'level', 'locationNode']),
            ['store_name', 'store_code'],
            function ($query) use ($request) {
                if ($request->filled('status')) {
                    $query->where('status', $request->input('status'));
                }
            },
            function (Store $item) {
                return [
                    'id' => $item->id,
                    'store' => '<div class="flex items-center gap-3">'
                        .'<div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">'
                        .svg('heroicon-o-building-storefront', 'h-5 w-5')->toHtml()
                        .'</div>'
                        .'<div><p class="font-semibold text-gray-900 dark:text-white">'.e($item->store_name).'</p><p class="text-xs text-gray-500 dark:text-gray-400">'.e($item->store_code).' · /'.e($item->slug).'</p></div>'
                        .'</div>',
                    'name_plain' => $item->store_name,
                    'owner' => e($item->owner->name ?? '-'),
                    'level' => $item->level ? Render::badge('blue', $item->level->name) : '<span class="text-gray-400">-</span>',
                    'status' => $item->status === 'active' ? Render::badge('success', 'Active') : Render::badge('danger', 'Inactive'),
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $item->id) : null,
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $item->id) : null,
                ];
            },
            ['store_name', 'store_code', 'status']
        );
    }

    protected function validateStore(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'store_level_id' => ['nullable', 'integer', 'exists:store_levels,id'],
            'location_node_id' => ['nullable', 'uuid', 'exists:location_nodes,id'],
            'store_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048', 'dimensions:ratio=1,min_width=240,min_height=240'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048', 'dimensions:min_width=1024,min_height=768'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'rate_per_km' => ['required', 'numeric', 'min:0'],
            'min_free_distance_km' => ['required', 'numeric', 'min:0'],
            'max_radius_km' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'hours' => ['nullable', 'array'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
        ], [
            'logo.dimensions' => 'Logo harus persegi (rasio 1:1) dengan ukuran minimal 240x240 piksel.',
            'banner.dimensions' => 'Banner minimal berukuran 1024x768 piksel.',
        ]);
    }

    protected function applyUploads(Request $request, array &$validated, ?Store $store = null): void
    {
        foreach (['logo', 'banner'] as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $path = Storage::disk('public')->putFile('stores', $request->file($field));
            $validated[$field] = $path;

            $oldPath = $store?->getAttribute($field);
            if ($oldPath && $oldPath !== $path) {
                Storage::disk('public')->delete($oldPath);
            }
        }
    }
}
