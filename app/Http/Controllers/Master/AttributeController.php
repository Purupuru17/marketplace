<?php

namespace App\Http\Controllers\Master;

use App\Models\Attribute;
use App\Services\Master\AttributeService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\Render;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttributeController extends BaseCoreController
{
    private $module = 'master.attribute';

    public function __construct(protected AttributeService $service) {}

    public function index(Request $request)
    {
        $columns = [
            ['key' => 'name', 'label' => 'Nama', 'sortable' => true, 'searchable' => true, 'html' => true, 'align' => 'left'],
            ['key' => 'values_count', 'label' => 'Jumlah Nilai', 'sortable' => true, 'html' => true, 'align' => 'center'],
        ];

        $compact = [
            'title' => 'Atribut',
            'subtitle' => 'Data Atribut',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Atribut']],

            'columns' => $columns,
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

    public function ajax(Request $request)
    {
        $type = $request->input('type');
        $source = $request->input('source');

        return match ($type) {
            'table' => match ($source) {
                'index' => $this->tableIndex($request),
                default => response()->json(['status' => 'error', 'message' => 'Sumber data tidak valid.'], 400),
            },
            default => response()->json(['status' => 'error', 'message' => 'Aksi tidak valid.'], 400),
        };
    }

    private function tableIndex(Request $request)
    {
        return DataTableService::process(
            $request,
            Attribute::withCount('values'),
            ['name'],
            null,
            function (Attribute $item) {
                return [
                    'id' => $item->id,
                    'name' => '<p class="font-semibold text-gray-900 dark:text-white">'.e($item->name).'</p><p class="text-xs text-gray-500 dark:text-gray-400">'.$item->values_count.' nilai</p>',
                    'name_plain' => $item->name,
                    'values_count' => Render::badge('blue', $item->values_count),
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $item->id) : null,
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $item->id) : null,
                ];
            },
            ['name', 'values_count']
        );
    }
}
