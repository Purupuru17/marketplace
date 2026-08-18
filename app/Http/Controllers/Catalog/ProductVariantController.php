<?php

namespace App\Http\Controllers\Catalog;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Catalog\ProductVariantService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\ActiveRole;
use IdCore\CoreStarter\Support\Render;
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

        $columns = [
            ['key' => 'sku', 'label' => 'SKU', 'sortable' => true, 'searchable' => true, 'align' => 'left'],
            ['key' => 'product', 'label' => 'Produk', 'align' => 'left'],
            ['key' => 'attribute', 'label' => 'Atribut', 'html' => true, 'align' => 'center'],
            ['key' => 'store', 'label' => 'Toko', 'align' => 'left'],
            ['key' => 'price', 'label' => 'Harga', 'sortable' => true, 'html' => true, 'align' => 'right'],
            ['key' => 'stock', 'label' => 'Stok', 'sortable' => true, 'html' => true, 'align' => 'center'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'html' => true, 'align' => 'center'],
        ];

        $compact = [
            'storeOptions' => $this->service->storeOptions($storeIds),
            'productOptions' => $this->service->productOptions($storeIds),

            'title' => 'Varian Produk',
            'subtitle' => 'Data Varian Produk',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Katalog'], ['Varian Produk']],

            'columns' => $columns,
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

        $this->service->create($validated, $request->file('variant_image'));

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Varian produk berhasil ditambahkan.');
    }

    public function edit(Request $request, ProductVariant $productVariant)
    {
        $this->authorizeOwnership($productVariant, $this->allowedStoreIds($request->user()));

        $productVariant->load(['attributeValues', 'images']);

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

        $this->service->update($productVariant, $validated, $request->file('variant_image'));

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
            ProductVariant::with(['product', 'store', 'attributeValues.attribute']),
            ['sku'],
            function ($query) use ($request, $storeIds) {
                if ($storeIds !== null) {
                    $query->whereIn('store_id', $storeIds);
                }
                if ($request->filled('store_id')) {
                    $query->where('store_id', $request->input('store_id'));
                }
                if ($request->filled('product_id')) {
                    $query->where('product_id', $request->input('product_id'));
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->input('status'));
                }
            },
            function (ProductVariant $item) {
                $attrs = $item->attributeValues
                    ->sortBy(fn ($v) => $v->attribute?->name)
                    ->map->value
                    ->join(' · ');

                return [
                    'id' => $item->id,
                    'sku' => '<p class="font-semibold text-gray-900 dark:text-white">'.e($item->sku).'</p>',
                    'sku_plain' => $item->sku,
                    'product' => e($item->product->name ?? '-'),
                    'attribute' => $attrs ? '<span class="text-sm text-gray-700 dark:text-gray-300">'.e($attrs).'</span>' : '<span class="text-gray-400">-</span>',
                    'store' => e($item->store->store_name ?? '-'),
                    'price' => '<span class="font-medium text-gray-900 dark:text-white">Rp '.number_format((float) $item->price, 0, ',', '.').'</span>',
                    'stock' => number_format($item->stock).' <span class="text-xs text-gray-400">· '.number_format($item->weight_grams).'g</span>',
                    'status' => $item->status === 'active' ? Render::badge('success', 'Active') : Render::badge('danger', 'Inactive'),
                    'name_plain' => $item->sku,
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $item->id) : null,
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $item->id) : null,
                ];
            },
            ['sku', 'price', 'stock', 'status']
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
            'variant_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048', 'dimensions:min_width=640,min_height=480'],
        ], [
            'variant_image.dimensions' => 'Gambar varian minimal berukuran 640x480 piksel.',
        ]);
    }
}
