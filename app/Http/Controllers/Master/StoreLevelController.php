<?php

namespace App\Http\Controllers\Master;

use App\Models\StoreLevel;
use App\Services\Master\StoreLevelService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\Render;
use Illuminate\Http\Request;

class StoreLevelController extends BaseCoreController
{
    private $module = 'master.store-level';

    public function __construct(protected StoreLevelService $service) {}

    public function index(Request $request)
    {
        $columns = [
            ['key' => 'name', 'label' => 'Nama', 'sortable' => true, 'searchable' => true, 'align' => 'left'],
            ['key' => 'price', 'label' => 'Harga', 'sortable' => true, 'align' => 'right'],
            ['key' => 'max_products', 'label' => 'Maks Produk', 'sortable' => true, 'align' => 'center'],
            ['key' => 'max_discount', 'label' => 'Maks Diskon', 'sortable' => true, 'align' => 'center'],
            ['key' => 'campaign', 'label' => 'Campaign', 'html' => true, 'align' => 'center'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'html' => true, 'align' => 'center'],
        ];

        $compact = [
            'title' => 'Store Level',
            'subtitle' => 'Data Store Level',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Store Level']],

            'columns' => $columns,
        ];

        return view($this->module.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData' => null,

            'title' => 'Tambah Store Level',
            'subtitle' => 'Atur level toko marketplace',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Store Level', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'max_products' => ['nullable', 'integer', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'can_run_campaign' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['can_run_campaign'] = $request->boolean('can_run_campaign');

        $this->service->create($validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Store level berhasil ditambahkan.');
    }

    public function edit(StoreLevel $storeLevel)
    {
        $compact = [
            'formData' => $storeLevel,

            'title' => 'Edit Store Level',
            'subtitle' => 'Atur level toko marketplace',

            'action' => route($this->module.'.update', $storeLevel->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Store Level', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function update(Request $request, StoreLevel $storeLevel)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'max_products' => ['nullable', 'integer', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'can_run_campaign' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['can_run_campaign'] = $request->boolean('can_run_campaign');

        $this->service->update($storeLevel, $validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Store level berhasil diperbarui.');
    }

    public function destroy(StoreLevel $storeLevel)
    {
        $this->service->delete($storeLevel);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Store level berhasil dihapus.');
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
            StoreLevel::query(),
            ['name'],
            null,
            function (StoreLevel $item) {
                return [
                    'id' => $item->id,
                    'name' => '<p class="font-semibold text-gray-900 dark:text-white">'.e($item->name).'</p><p class="text-xs text-gray-500 dark:text-gray-400">Urutan: '.$item->sort_order.'</p>',
                    'name_plain' => $item->name,
                    'price' => 'Rp '.number_format($item->price, 0, ',', '.'),
                    'max_products' => $item->max_products ?? '-',
                    'max_discount' => $item->max_discount ? $item->max_discount.'%' : '-',
                    'campaign' => $item->can_run_campaign ? Render::badge('success', 'Ya') : Render::badge('gray', 'Tidak'),
                    'status' => $item->status === 'active' ? Render::badge('success', 'Active') : Render::badge('danger', 'Inactive'),
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $item->id) : null,
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $item->id) : null,
                ];
            },
            ['name', 'price', 'max_products', 'max_discount', 'can_run_campaign', 'sort_order', 'status']
        );
    }
}
