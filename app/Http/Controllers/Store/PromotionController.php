<?php

namespace App\Http\Controllers\Store;

use App\Models\Promotion;
use App\Services\Store\PromotionService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\Render;
use Illuminate\Http\Request;

class PromotionController extends BaseCoreController
{
    private $module = 'toko.promotion';

    protected $view = 'store.promotion';

    public function __construct(protected PromotionService $service) {}

    public function index(Request $request)
    {
        $columns = [
            ['key' => 'name', 'label' => 'Promo', 'sortable' => true, 'searchable' => true, 'html' => true, 'align' => 'left'],
            ['key' => 'source', 'label' => 'Sumber', 'html' => true, 'align' => 'center'],
            ['key' => 'discount', 'label' => 'Diskon', 'html' => true, 'align' => 'center'],
            ['key' => 'periode', 'label' => 'Periode', 'align' => 'center'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'html' => true, 'align' => 'center'],
        ];

        $compact = [
            'title' => 'Promo',
            'subtitle' => 'Data Promo',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Promo']],

            'columns' => $columns,
        ];

        return view($this->view.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData' => null,
            'storeOptions' => $this->service->storeOptions(auth()->user()),
            'productOptions' => $this->service->productOptions(auth()->user()),
            'selectedProducts' => collect(),
            'isAdmin' => auth()->user()->hasRole('Administrator'),

            'title' => 'Tambah Promo',
            'subtitle' => 'Diskon otomatis untuk produk terpilih',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Promo', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->view.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePromotion($request);

        $this->service->create(auth()->user(), $validated, $request->input('products', []));

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Promo berhasil ditambahkan.');
    }

    public function edit(Promotion $promotion)
    {
        $this->service->authorize(auth()->user(), $promotion);

        $promotion->load('products');

        $compact = [
            'formData' => $promotion,
            'storeOptions' => $this->service->storeOptions(auth()->user()),
            'productOptions' => $this->service->productOptions(auth()->user()),
            'selectedProducts' => $promotion->products->pluck('id'),
            'isAdmin' => auth()->user()->hasRole('Administrator'),

            'title' => 'Edit Promo',
            'subtitle' => 'Diskon otomatis untuk produk terpilih',

            'action' => route($this->module.'.update', $promotion->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Promo', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->view.'.form', $compact);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $this->validatePromotion($request);

        $this->service->update(auth()->user(), $promotion, $validated, $request->input('products', []));

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(Promotion $promotion)
    {
        $this->service->delete(auth()->user(), $promotion);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Promo berhasil dihapus.');
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
        $user = auth()->user();

        return DataTableService::process(
            $request,
            Promotion::query()->with(['store', 'products']),
            ['name'],
            function ($query) use ($request, $user) {
                if (! $user->hasRole('Administrator')) {
                    $storeIds = $user->stores()->pluck('id');

                    $query->where(function ($q) use ($storeIds) {
                        $q->where('source', 'store')->whereIn('store_id', $storeIds)
                            ->orWhere('source', 'platform');
                    });
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->input('status'));
                }
            },
            function (Promotion $item) {
                $discount = $item->type === 'percentage'
                    ? rtrim(rtrim(number_format($item->value, 2, ',', '.'), '0'), ',').'%'
                    : 'Rp '.number_format($item->value, 0, ',', '.');

                return [
                    'id' => $item->id,
                    'name' => '<p class="font-semibold text-gray-900 dark:text-white">'.e($item->name).'</p>'
                        .'<p class="text-xs text-gray-500 dark:text-gray-400">'
                        .e($item->source === 'platform' ? 'Platform' : ($item->store->store_name ?? '-'))
                        .' · '.($item->products_count ?? $item->products->count()).' produk</p>',
                    'name_plain' => $item->name,
                    'source' => $item->source === 'platform' ? Render::badge('blue', 'Platform') : Render::badge('brand', 'Toko'),
                    'discount' => '<span class="font-semibold text-gray-900 dark:text-white">'.e($discount).'</span>',
                    'periode' => '<span class="text-xs text-gray-500 dark:text-gray-400">'.$item->starts_at?->translatedFormat('d M Y').' — '.$item->ends_at?->translatedFormat('d M Y').'</span>',
                    'status' => $item->status === 'active' ? Render::badge('success', 'Active') : Render::badge('danger', 'Inactive'),
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $item->id) : null,
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $item->id) : null,
                ];
            },
            ['name', 'status', 'created_at']
        );
    }

    protected function validatePromotion(Request $request): array
    {
        return $request->validate([
            'store_id' => ['nullable', 'uuid', 'exists:stores,id'],
            'name' => ['required', 'string', 'max:255'],
            'source' => ['required', 'in:store,platform'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'stackable' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
            'products' => ['nullable', 'array'],
            'products.*' => ['uuid', 'exists:products,id'],
        ]);
    }
}
