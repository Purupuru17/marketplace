<?php

namespace App\Http\Controllers\Master;

use App\Models\Attribute;
use App\Services\Master\AttributeService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttributeController extends BaseCoreController
{
    private $module = 'master.attribute';

    public function __construct(protected AttributeService $service) {}

    public function index(Request $request)
    {
        $attributes = $this->service->paginate(
            $request->only(['search']),
            (int) $request->input('per_page', 10)
        );

        $compact = [
            'listData' => $attributes,

            'title' => 'Atribut',
            'subtitle' => 'Data Atribut',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Atribut']],
        ];

        return view($this->module.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData' => null,
            'title' => 'Tambah Atribut',
            'subtitle' => 'Atribut sebagai dimensi varian produk',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Atribut', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->module.'.form', $compact);
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
            ->route($this->module.'.index')
            ->with('success', 'Atribut berhasil ditambahkan.');
    }

    public function edit(Attribute $attribute)
    {
        $attribute->load('values');

        $compact = [
            'formData' => $attribute,

            'title' => 'Edit Atribut',
            'subtitle' => 'Atribut sebagai dimensi varian produk',

            'action' => route($this->module.'.update', $attribute->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Atribut', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->module.'.form', $compact);
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
            ->route($this->module.'.index')
            ->with('success', 'Atribut berhasil diperbarui.');
    }

    public function destroy(Attribute $attribute)
    {
        $this->service->delete($attribute);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Atribut berhasil dihapus.');
    }
}
