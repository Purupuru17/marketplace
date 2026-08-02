<?php

namespace App\Http\Controllers\Master;

use App\Models\CustomerLevel;
use App\Services\Master\CustomerLevelService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class CustomerLevelController extends BaseCoreController
{
    public function __construct(protected CustomerLevelService $service) {}

    public function index(Request $request)
    {
        $customerLevels = $this->service->paginate(
            $request->only(['search']),
            (int) $request->input('per_page', 10)
        );

        return view('master.customer-level.index', compact('customerLevels'));
    }

    public function create()
    {
        return view('master.customer-level.form', ['customerLevel' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'minimum_points' => ['required', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'benefit' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->create($validated);

        return redirect()
            ->route('master.customer-level.index')
            ->with('success', 'Customer level berhasil ditambahkan.');
    }

    public function edit(CustomerLevel $customerLevel)
    {
        return view('master.customer-level.form', compact('customerLevel'));
    }

    public function update(Request $request, CustomerLevel $customerLevel)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'minimum_points' => ['required', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'benefit' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->update($customerLevel, $validated);

        return redirect()
            ->route('master.customer-level.index')
            ->with('success', 'Customer level berhasil diperbarui.');
    }

    public function destroy(CustomerLevel $customerLevel)
    {
        $this->service->delete($customerLevel);

        return redirect()
            ->route('master.customer-level.index')
            ->with('success', 'Customer level berhasil dihapus.');
    }
}
