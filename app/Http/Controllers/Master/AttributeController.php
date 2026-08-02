<?php

namespace App\Http\Controllers\Master;

use App\Models\Attribute;
use App\Services\Master\AttributeService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttributeController extends BaseCoreController
{
    public function __construct(protected AttributeService $service) {}

    public function index(Request $request)
    {
        $attributes = $this->service->paginate(
            $request->only(['search']),
            (int) $request->input('per_page', 10)
        );

        return view('master.attribute.index', compact('attributes'));
    }

    public function create()
    {
        return view('master.attribute.form', ['attribute' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:attributes,name'],
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:255'],
        ]);

        $this->service->create($validated);

        return redirect()
            ->route('master.attribute.index')
            ->with('success', 'Atribut berhasil ditambahkan.');
    }

    public function edit(Attribute $attribute)
    {
        $attribute->load('values');

        return view('master.attribute.form', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('attributes', 'name')->ignore($attribute->id)],
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:255'],
        ]);

        $this->service->update($attribute, $validated);

        return redirect()
            ->route('master.attribute.index')
            ->with('success', 'Atribut berhasil diperbarui.');
    }

    public function destroy(Attribute $attribute)
    {
        $this->service->delete($attribute);

        return redirect()
            ->route('master.attribute.index')
            ->with('success', 'Atribut berhasil dihapus.');
    }
}
