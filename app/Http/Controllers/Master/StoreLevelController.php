<?php

namespace App\Http\Controllers\Master;

use App\Models\StoreLevel;
use App\Services\Master\StoreLevelService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class StoreLevelController extends BaseCoreController
{
    public function __construct(protected StoreLevelService $service) {}

    public function index(Request $request)
    {
        $storeLevels = $this->service->paginate(
            $request->only(['search']),
            (int) $request->input('per_page', 10)
        );

        return view('master.store-level.index', compact('storeLevels'));
    }

    public function create()
    {
        return view('master.store-level.form', ['storeLevel' => null]);
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
            ->route('master.store-level.index')
            ->with('success', 'Store level berhasil ditambahkan.');
    }

    public function edit(StoreLevel $storeLevel)
    {
        return view('master.store-level.form', compact('storeLevel'));
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
            ->route('master.store-level.index')
            ->with('success', 'Store level berhasil diperbarui.');
    }

    public function destroy(StoreLevel $storeLevel)
    {
        $this->service->delete($storeLevel);

        return redirect()
            ->route('master.store-level.index')
            ->with('success', 'Store level berhasil dihapus.');
    }
}
