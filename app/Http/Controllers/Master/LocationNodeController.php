<?php

namespace App\Http\Controllers\Master;

use App\Models\LocationNode;
use App\Services\Master\LocationNodeService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class LocationNodeController extends BaseCoreController
{
    private $module = 'master.location-node';

    public function __construct(protected LocationNodeService $service) {}

    public function index(Request $request)
    {
        $locationNodes = $this->service->paginate(
            $request->only(['search']),
            (int) $request->input('per_page', 10)
        );

        $compact = [
            'listData'   => $locationNodes,

            'title'      => 'Node Lokasi',
            'subtitle'   => 'Data Node Lokasi',

            'module'     => $this->module,
            'rolesName'  => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Node Lokasi']],
        ];

        return view($this->module.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData'   => null,

            'title'      => 'Tambah Node Lokasi',
            'subtitle'   => 'Titik lokasi untuk perhitungan ongkir',

            'action'     => route($this->module.'.store'),
            'module'     => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Node Lokasi', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->create($validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Node lokasi berhasil ditambahkan.');
    }

    public function edit(LocationNode $locationNode)
    {
        $compact = [
            'formData'   => $locationNode,

            'title'      => 'Edit Node Lokasi',
            'subtitle'   => 'Titik lokasi untuk perhitungan ongkir',
            
            'action'     => route($this->module.'.update', $locationNode->id),
            'module'     => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Node Lokasi', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function update(Request $request, LocationNode $locationNode)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->update($locationNode, $validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Node lokasi berhasil diperbarui.');
    }

    public function destroy(LocationNode $locationNode)
    {
        $this->service->delete($locationNode);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Node lokasi berhasil dihapus.');
    }
}
