<?php

namespace App\Http\Controllers\Master;

use App\Models\Category;
use App\Services\Master\CategoryService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends BaseCoreController
{
    public function __construct(protected CategoryService $service) {}

    public function index(Request $request)
    {
        $categories = $this->service->paginate(
            $request->only(['search', 'parent_id']),
            (int) $request->input('per_page', 10)
        );

        $parentOptions = $this->service->options();

        return view('master.category.index', compact('categories', 'parentOptions'));
    }

    public function create()
    {
        $parentOptions = $this->service->options();

        return view('master.category.form', ['category' => null, 'parentOptions' => $parentOptions]);
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
            ->route('master.category.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        $parentOptions = $this->service->options($category);

        return view('master.category.form', compact('category', 'parentOptions'));
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
            ->route('master.category.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $this->service->delete($category);

        return redirect()
            ->route('master.category.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
