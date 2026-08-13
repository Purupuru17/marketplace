<?php

namespace App\Http\Controllers\Master;

use App\Models\LocationDistance;
use App\Services\Master\LocationDistanceService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class LocationDistanceController extends BaseCoreController
{
    private $module = 'master.location-distance';

    public function __construct(protected LocationDistanceService $service) {}

    public function index(Request $request)
    {
        $locationDistances = $this->service->paginate(
            $request->only(['search']),
            (int) $request->input('per_page', 10)
        );

        $compact = [
            'listData' => $locationDistances,

            'title' => 'Jarak Antar Node',
            'subtitle' => 'Data Jarak Antar Node',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Jarak Antar Node']],
        ];

        return view($this->module.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData' => null,
            'nodeOptions' => $this->service->nodeOptions(),

            'title' => 'Tambah Jarak Antar Node',
            'subtitle' => 'Jarak antar node untuk grafik ongkir',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Jarak Antar Node', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'origin_node_id' => ['required', 'uuid', 'exists:location_nodes,id'],
            'destination_node_id' => ['required', 'uuid', 'exists:location_nodes,id', 'different:origin_node_id'],
            'distance_km' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
        ]);

        if ($this->service->pairExists($validated['origin_node_id'], $validated['destination_node_id'])) {
            return back()
                ->withErrors(['destination_node_id' => 'Jarak antar kedua node ini sudah tercatat.'])
                ->withInput();
        }

        $this->service->create($validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Jarak antar node berhasil ditambahkan.');
    }

    public function edit(LocationDistance $locationDistance)
    {
        $locationDistance->load(['origin', 'destination']);

        $compact = [
            'formData' => $locationDistance,
            'nodeOptions' => $this->service->nodeOptions(),

            'title' => 'Edit Jarak Antar Node',
            'subtitle' => 'Jarak antar node untuk grafik ongkir',

            'action' => route($this->module.'.update', $locationDistance->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Jarak Antar Node', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function update(Request $request, LocationDistance $locationDistance)
    {
        $validated = $request->validate([
            'origin_node_id' => ['required', 'uuid', 'exists:location_nodes,id'],
            'destination_node_id' => ['required', 'uuid', 'exists:location_nodes,id', 'different:origin_node_id'],
            'distance_km' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
        ]);

        if ($this->service->pairExists($validated['origin_node_id'], $validated['destination_node_id'], $locationDistance->id)) {
            return back()
                ->withErrors(['destination_node_id' => 'Jarak antar kedua node ini sudah tercatat.'])
                ->withInput();
        }

        $this->service->update($locationDistance, $validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Jarak antar node berhasil diperbarui.');
    }

    public function destroy(LocationDistance $locationDistance)
    {
        $this->service->delete($locationDistance);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Jarak antar node berhasil dihapus.');
    }
}
