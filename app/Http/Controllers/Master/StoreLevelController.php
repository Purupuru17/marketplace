<?php

namespace App\Http\Controllers\Master;

use App\Models\StoreLevel;
use App\Services\Master\StoreLevelService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class StoreLevelController extends BaseCoreController
{
    private $module = 'master.store-level';

    public function __construct(protected StoreLevelService $service) {}

    public function index(Request $request)
    {
        $storeLevels = $this->service->paginate(
            $request->only(['search']),
            (int) $request->input('per_page', 10)
        );

        $compact = [
            'listData'   => $storeLevels,

            'title'      => 'Store Level',
            'subtitle'   => 'Data Store Level',

            'module'     => $this->module,
            'rolesName'  => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Store Level']],
        ];

        return view($this->module.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData'   => null,

            'title'      => 'Tambah Store Level',
            'subtitle'   => 'Atur level toko marketplace',

            'action'     => route($this->module.'.store'),
            'module'     => $this->module,
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
            'formData'   => $storeLevel,

            'title'      => 'Edit Store Level',
            'subtitle'   => 'Atur level toko marketplace',
            
            'action'     => route($this->module.'.update', $storeLevel->id),
            'module'     => $this->module,
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
}
