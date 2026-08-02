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

        return view('catalog.product.index', [
            'products' => $products,
            'storeOptions' => $this->service->storeOptions($storeIds),
        ]);
    }

    public function create(Request $request)
    {
        return view('catalog.product.form', [
            'product' => null,
            'storeOptions' => $this->service->storeOptions($this->allowedStoreIds($request->user())),
            'categoryOptions' => $this->categoryService->options(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request, $this->allowedStoreIds($request->user()));

        $this->service->create($validated);

        return redirect()
            ->route('katalog.product.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Request $request, Product $product)
    {
        $this->authorizeOwnership($product, $this->allowedStoreIds($request->user()));

        return view('catalog.product.form', [
            'product' => $product,
            'storeOptions' => $this->service->storeOptions($this->allowedStoreIds($request->user())),
            'categoryOptions' => $this->categoryService->options(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $storeIds = $this->allowedStoreIds($request->user());
        $this->authorizeOwnership($product, $storeIds);

        $validated = $this->validateProduct($request, $storeIds, $product->id);

        $this->service->update($product, $validated);

        return redirect()
            ->route('katalog.product.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorizeOwnership($product, $this->allowedStoreIds($request->user()));

        $this->service->delete($product);

        return redirect()
            ->route('katalog.product.index')
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
