<?php

namespace App\Http\Controllers\Store;

use App\Models\Store;
use App\Services\Store\StoreService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class StoreController extends BaseCoreController
{
    public function __construct(protected StoreService $service) {}

    public function index(Request $request)
    {
        $stores = $this->service->paginate(
            $request->only(['search', 'status']),
            (int) $request->input('per_page', 10)
        );

        return view('store.store.index', compact('stores'));
    }

    public function create()
    {
        return view('store.store.form', [
            'store' => null,
            'userOptions' => $this->service->userOptions(),
            'levelOptions' => $this->service->levelOptions(),
            'nodeOptions' => $this->service->nodeOptions(),
            'hoursByDay' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateStore($request);

        $this->service->create($validated, $request->input('hours', []));

        return redirect()
            ->route('toko.store.index')
            ->with('success', 'Toko berhasil ditambahkan.');
    }

    public function edit(Store $store)
    {
        $store->load('operatingHours');

        return view('store.store.form', [
            'store' => $store,
            'userOptions' => $this->service->userOptions(),
            'levelOptions' => $this->service->levelOptions(),
            'nodeOptions' => $this->service->nodeOptions(),
            'hoursByDay' => $store->operatingHours->keyBy('day'),
        ]);
    }

    public function update(Request $request, Store $store)
    {
        $validated = $this->validateStore($request);

        $this->service->update($store, $validated, $request->input('hours', []));

        return redirect()
            ->route('toko.store.index')
            ->with('success', 'Toko berhasil diperbarui.');
    }

    public function destroy(Store $store)
    {
        $this->service->delete($store);

        return redirect()
            ->route('toko.store.index')
            ->with('success', 'Toko berhasil dihapus.');
    }

    protected function validateStore(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'store_level_id' => ['nullable', 'integer', 'exists:store_levels,id'],
            'location_node_id' => ['nullable', 'uuid', 'exists:location_nodes,id'],
            'store_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'rate_per_km' => ['required', 'numeric', 'min:0'],
            'min_free_distance_km' => ['required', 'numeric', 'min:0'],
            'max_radius_km' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'hours' => ['nullable', 'array'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
        ]);
    }
}
