<?php

namespace App\Http\Controllers\Master;

use App\Models\LocationNode;
use App\Services\Master\LocationNodeService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class LocationNodeController extends BaseCoreController
{
    public function __construct(protected LocationNodeService $service) {}

    public function index(Request $request)
    {
        $locationNodes = $this->service->paginate(
            $request->only(['search']),
            (int) $request->input('per_page', 10)
        );

        return view('master.location-node.index', compact('locationNodes'));
    }

    public function create()
    {
        return view('master.location-node.form', ['locationNode' => null]);
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
            ->route('master.location-node.index')
            ->with('success', 'Node lokasi berhasil ditambahkan.');
    }

    public function edit(LocationNode $locationNode)
    {
        return view('master.location-node.form', compact('locationNode'));
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
            ->route('master.location-node.index')
            ->with('success', 'Node lokasi berhasil diperbarui.');
    }

    public function destroy(LocationNode $locationNode)
    {
        $this->service->delete($locationNode);

        return redirect()
            ->route('master.location-node.index')
            ->with('success', 'Node lokasi berhasil dihapus.');
    }
}
