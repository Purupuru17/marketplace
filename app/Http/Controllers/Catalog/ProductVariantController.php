<?php

namespace App\Http\Controllers\Catalog;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Catalog\ProductVariantService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Support\ActiveRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductVariantController extends BaseCoreController
{
    private $module = 'katalog.product-variant';

    protected $view = 'catalog.product-variant';

    public function __construct(protected ProductVariantService $service) {}

    public function index(Request $request)
    {
        $storeIds = $this->allowedStoreIds($request->user());

        $variants = $this->service->paginate(
            $request->only(['search', 'status', 'store_id', 'product_id']),
            $storeIds,
            (int) $request->input('per_page', 10)
        );

        $compact = [
            'listData' => $variants,
            'storeOptions' => $this->service->storeOptions($storeIds),
            'productOptions' => $this->service->productOptions($storeIds),

            'title' => 'Varian Produk',
            'subtitle' => 'Data Varian Produk',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Katalog'], ['Varian Produk']],
        ];

        return view($this->view.'.index', $compact);
    }

    public function create(Request $request)
    {
        $compact = [
            'formData' => null,
            'productOptions' => $this->service->productOptions($this->allowedStoreIds($request->user())),
            'attributeGroups' => $this->service->attributeGroups(),

            'title' => 'Tambah Varian',
            'subtitle' => 'Data varian produk milik toko tertentu',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Katalog'], ['Varian Produk', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->view.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $this->validateVariant($request, $this->allowedStoreIds($request->user()));

        $this->service->create($validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Varian produk berhasil ditambahkan.');
    }

    public function edit(Request $request, ProductVariant $productVariant)
    {
        $this->authorizeOwnership($productVariant, $this->allowedStoreIds($request->user()));

        $productVariant->load('attributeValues');

        $compact = [
            'formData' => $productVariant,
            'productOptions' => $this->service->productOptions($this->allowedStoreIds($request->user())),
            'attributeGroups' => $this->service->attributeGroups(),

            'title' => 'Edit Varian Produk',
            'subtitle' => 'Data varian produk milik toko tertentu',

            'action' => route($this->module.'.update', $productVariant->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Katalog'], ['Varian Produk', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->view.'.form', $compact);
    }

    public function update(Request $request, ProductVariant $productVariant)
    {
        $storeIds = $this->allowedStoreIds($request->user());
        $this->authorizeOwnership($productVariant, $storeIds);

        $validated = $this->validateVariant($request, $storeIds, $productVariant->id);

        $this->service->update($productVariant, $validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Varian produk berhasil diperbarui.');
    }

    public function destroy(Request $request, ProductVariant $productVariant)
    {
        $this->authorizeOwnership($productVariant, $this->allowedStoreIds($request->user()));

        $this->service->delete($productVariant);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Varian produk berhasil dihapus.');
    }

    protected function allowedStoreIds($user): ?array
    {
        $role = ActiveRole::get($user);

        if ($role && $role->name === 'Toko') {
            return $user->stores()->pluck('id')->all();
        }

        return null;
    }

    protected function authorizeOwnership(ProductVariant $variant, ?array $storeIds): void
    {
        abort_unless($storeIds === null || in_array($variant->store_id, $storeIds), 403);
    }

    protected function validateVariant(Request $request, ?array $storeIds = null, ?string $ignoreId = null): array
    {
        $productRule = ['required', 'uuid', 'exists:products,id'];

        if ($storeIds !== null) {
            $productRule[] = Rule::exists('products', 'id')->whereIn('store_id', $storeIds);
        }

        $skuRule = ['required', 'string', 'max:100'];

        if ($request->filled('product_id')) {
            $storeId = Product::whereKey($request->input('product_id'))->value('store_id');

            if ($storeId) {
                $skuRule[] = Rule::unique('product_variants', 'sku')
                    ->where('store_id', $storeId)
                    ->ignore($ignoreId);
            }
        }

        return $request->validate([
            'product_id' => $productRule,
            'sku' => $skuRule,
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight_grams' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'attribute_value_ids' => [
                'nullable', 'array',
                function ($attribute, $value, $fail) {
                    if (! is_array($value) || empty($value)) {
                        return;
                    }

                    $ids = array_values(array_unique(array_filter($value)));

                    $distinctAttributes = AttributeValue::whereIn('id', $ids)->distinct()->count('attribute_id');

                    if (count($ids) !== $distinctAttributes) {
                        $fail('Tidak boleh memilih lebih dari satu nilai pada atribut yang sama.');
                    }
                },
            ],
            'attribute_value_ids.*' => ['uuid', 'exists:attribute_values,id'],
        ]);
    }
}
