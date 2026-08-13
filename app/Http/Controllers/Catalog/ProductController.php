<?php

namespace App\Http\Controllers\Catalog;

use App\Models\Product;
use App\Services\Catalog\ProductService;
use App\Services\Master\CategoryService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Support\ActiveRole;
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

        $products = $this->service->paginate(
            $request->only(['search', 'status', 'store_id']),
            $storeIds,
            (int) $request->input('per_page', 10)
        );

        $compact = [
            'listData' => $products,
            'storeOptions' => $this->service->storeOptions($storeIds),

            'title' => 'Produk',
            'subtitle' => 'Data Produk',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Katalog'], ['Produk']],

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
