<?php

namespace App\Http\Controllers\Master;

use App\Models\LocationDistance;
use App\Services\Master\LocationDistanceService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class LocationDistanceController extends BaseCoreController
{
    public function __construct(protected LocationDistanceService $service) {}

    public function index(Request $request)
    {
        $locationDistances = $this->service->paginate(
            $request->only(['search']),
            (int) $request->input('per_page', 10)
        );

        return view('master.location-distance.index', compact('locationDistances'));
    }

    public function create()
    {
        return view('master.location-distance.form', ['locationDistance' => null, 'nodeOptions' => $this->service->nodeOptions()]);
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
            ->route('master.location-distance.index')
            ->with('success', 'Jarak antar node berhasil ditambahkan.');
    }

    public function edit(LocationDistance $locationDistance)
    {
        $locationDistance->load(['origin', 'destination']);

        return view('master.location-distance.form', [
            'locationDistance' => $locationDistance,
            'nodeOptions' => $this->service->nodeOptions(),
        ]);
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
            ->route('master.location-distance.index')
            ->with('success', 'Jarak antar node berhasil diperbarui.');
    }

    public function destroy(LocationDistance $locationDistance)
    {
        $this->service->delete($locationDistance);

        return redirect()
            ->route('master.location-distance.index')
            ->with('success', 'Jarak antar node berhasil dihapus.');
    }
}
