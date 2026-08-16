<?php

namespace App\Http\Controllers\Catalog;

use App\Models\Product;
use App\Services\Catalog\ProductService;
use App\Services\Master\CategoryService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\ActiveRole;
use IdCore\CoreStarter\Support\Render;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends BaseCoreController
{
    private $module = 'katalog.product';

    protected $view = 'catalog.product';

    public function __construct(
        protected ProductService $service,
        protected CategoryService $categoryService,
    ) {}

    public function index(Request $request)
    {
        $storeIds = $this->allowedStoreIds($request->user());

        $columns = [
            ['key' => 'product', 'label' => 'Produk', 'html' => true, 'align' => 'left'],
            ['key' => 'store', 'label' => 'Toko', 'align' => 'left'],
            ['key' => 'category', 'label' => 'Kategori', 'html' => true, 'align' => 'center'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'html' => true, 'align' => 'center'],
        ];

        $compact = [
            'storeOptions' => $this->service->storeOptions($storeIds),

            'title' => 'Produk',
            'subtitle' => 'Data Produk',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Katalog'], ['Produk']],

            'columns' => $columns,
        ];

        return view($this->view.'.index', $compact);
    }

    public function create(Request $request)
    {
        $compact = [
            'formData' => null,
            'storeOptions' => $this->service->storeOptions($this->allowedStoreIds($request->user())),
            'categoryOptions' => $this->categoryService->options(),

            'title' => 'Tambah Produk',
            'subtitle' => 'Data produk milik toko tertentu',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Katalog'], ['Produk', route($this->module.'.index')], ['Tambah Data']],

        ];

        return view($this->view.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request, $this->allowedStoreIds($request->user()));

        $this->service->create($validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Request $request, Product $product)
    {
        $this->authorizeOwnership($product, $this->allowedStoreIds($request->user()));

        $compact = [
            'formData' => $product,
            'storeOptions' => $this->service->storeOptions($this->allowedStoreIds($request->user())),
            'categoryOptions' => $this->categoryService->options(),

            'title' => 'Edit Produk',
            'subtitle' => 'Data produk milik toko tertentu',

            'action' => route($this->module.'.update', $product->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Katalog'], ['Produk', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->view.'.form', $compact);
    }

    public function update(Request $request, Product $product)
    {
        $storeIds = $this->allowedStoreIds($request->user());
        $this->authorizeOwnership($product, $storeIds);

        $validated = $this->validateProduct($request, $storeIds, $product->id);

        $this->service->update($product, $validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorizeOwnership($product, $this->allowedStoreIds($request->user()));

        $this->service->delete($product);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Produk berhasil dihapus.');
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
        $storeIds = $this->allowedStoreIds($request->user());

        return DataTableService::process(
            $request,
            Product::with(['store', 'category']),
            ['name', 'slug'],
            function ($query) use ($request, $storeIds) {
                if ($storeIds !== null) {
                    $query->whereIn('store_id', $storeIds);
                }
                if ($request->filled('store_id')) {
                    $query->where('store_id', $request->input('store_id'));
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->input('status'));
                }
            },
            function (Product $item) {
                $category = $item->category
                    ? '<span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">'.e($item->category->name).'</span>'
                    : '<span class="text-gray-400">-</span>';

                return [
                    'id' => $item->id,
                    'product' => '<div><p class="font-semibold text-gray-900 dark:text-white">'.e($item->name).'</p><p class="text-xs text-gray-500 dark:text-gray-400">/'.e($item->slug).'</p></div>',
                    'name_plain' => $item->name,
                    'store' => e($item->store->store_name ?? '-'),
                    'category' => $category,
                    'status' => $item->status === 'active' ? Render::badge('success', 'Active') : Render::badge('danger', 'Inactive'),
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $item->id) : null,
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $item->id) : null,
                ];
            },
            ['name', 'slug', 'status']
        );
    }

    protected function allowedStoreIds($user): ?array
    {
        $role = ActiveRole::get($user);

        if ($role && $role->name === 'Toko') {
            return $user->stores()->pluck('id')->all();
        }

        return null;
    }

    protected function authorizeOwnership(Product $product, ?array $storeIds): void
    {
        abort_unless($storeIds === null || in_array($product->store_id, $storeIds), 403);
    }

    protected function validateProduct(Request $request, ?array $storeIds = null): array
    {
        $storeRule = ['required', 'exists:stores,id'];

        if ($storeIds !== null) {
            $storeRule[] = Rule::in($storeIds);
        }

        return $request->validate([
            'store_id' => $storeRule,
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'is_featured' => ['nullable', 'boolean'],
        ]);
    }
}
