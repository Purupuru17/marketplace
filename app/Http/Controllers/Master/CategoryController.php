<?php

namespace App\Http\Controllers\Master;

use App\Models\Category;
use App\Services\Master\CategoryService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\Render;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends BaseCoreController
{
    private $module = 'master.category';

    public function __construct(protected CategoryService $service) {}

    public function index(Request $request)
    {
        $columns = [
            ['key' => 'name', 'label' => 'Nama', 'sortable' => true, 'searchable' => true, 'html' => true, 'align' => 'left'],
            ['key' => 'parent', 'label' => 'Parent', 'align' => 'left'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'html' => true, 'align' => 'center'],
        ];

        $compact = [
            'parentOptions' => $this->service->options(),

            'title' => 'Kategori',
            'subtitle' => 'Data Kategori',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Kategori']],

            'columns' => $columns,
        ];

        return view($this->module.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData' => null,
            'parentOptions' => $this->service->options(),

            'title' => 'Tambah Kategori',
            'subtitle' => 'Kategori untuk mengelompokkan produk',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Kategori', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->create($validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        $compact = [
            'formData' => $category,
            'parentOptions' => $this->service->options($category),

            'title' => 'Edit Kategori',
            'subtitle' => 'Kategori untuk mengelompokkan produk',

            'action' => route($this->module.'.update', $category->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Kategori', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->module.'.form', $compact);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'uuid',
                'exists:categories,id',
                Rule::notIn($this->service->excludedParentIds($category)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->update($category, $validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $this->service->delete($category);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Kategori berhasil dihapus.');
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
            Category::with('parent'),
            ['name'],
            function ($query) use ($request) {
                if ($request->filled('parent_id')) {
                    $query->where('parent_id', $request->input('parent_id'));
                }
            },
            function (Category $item) {
                return [
                    'id' => $item->id,
                    'name' => '<p class="font-semibold text-gray-900 dark:text-white">'.e($item->name).'</p><p class="text-xs text-gray-500 dark:text-gray-400">/'.e($item->slug).'</p>',
                    'name_plain' => $item->name,
                    'parent' => e($item->parent->name ?? '-'),
                    'status' => $item->status === 'active' ? Render::badge('success', 'Active') : Render::badge('danger', 'Inactive'),
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $item->id) : null,
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $item->id) : null,
                ];
            },
            ['name', 'slug', 'sort_order', 'status']
        );
    }
}
