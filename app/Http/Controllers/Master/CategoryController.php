<?php

namespace App\Http\Controllers\Master;

use App\Models\Category;
use App\Services\Master\CategoryService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends BaseCoreController
{
    private $module = 'master.category';

    public function __construct(protected CategoryService $service) {}

    public function index(Request $request)
    {
        $categories = $this->service->paginate(
            $request->only(['search', 'parent_id']),
            (int) $request->input('per_page', 10)
        );

        $compact = [
            'listData' => $categories,
            'parentOptions' => $this->service->options(),

            'title' => 'Kategori',
            'subtitle' => 'Data Kategori',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Master Data'], ['Kategori']],
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
}
