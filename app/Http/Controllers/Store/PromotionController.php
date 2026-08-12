<?php

namespace App\Http\Controllers\Store;

use App\Models\Promotion;
use App\Services\Store\PromotionService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class PromotionController extends BaseCoreController
{
    private $module = 'toko.promotion';

    protected $view = 'store.promotion';

    public function __construct(protected PromotionService $service) {}

    public function index(Request $request)
    {
        $promotions = $this->service->paginate(
            auth()->user(),
            $request->only(['search', 'status']),
            (int) $request->input('per_page', 10)
        );

        $compact = [
            'listData'   => $promotions,

            'title'      => 'Promo',
            'subtitle'   => 'Data Promo',

            'module'     => $this->module,
            'rolesName'  => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Promo']],
        ];

        return view($this->view.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData'         => null,
            'storeOptions'     => $this->service->storeOptions(auth()->user()),
            'productOptions'   => $this->service->productOptions(auth()->user()),
            'selectedProducts' => collect(),
            'isAdmin'          => auth()->user()->hasRole('Administrator'),

            'title'            => 'Tambah Promo',
            'subtitle'         => 'Diskon otomatis untuk produk terpilih',

            'action'           => route($this->module.'.store'),
            'module'           => $this->module,
            'breadcrumb'       => [['Beranda', route('dashboard')], ['Toko'], ['Promo', route($this->module.'.index')], ['Tambah Data']],
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
            'formData'         => $promotion,
            'storeOptions'     => $this->service->storeOptions(auth()->user()),
            'productOptions'   => $this->service->productOptions(auth()->user()),
            'selectedProducts' => $promotion->products->pluck('id'),
            'isAdmin'          => auth()->user()->hasRole('Administrator'),

            'title'            => 'Edit Promo',
            'subtitle'         => 'Diskon otomatis untuk produk terpilih',

            'action'           => route($this->module.'.update', $promotion->id),
            'module'           => $this->module,
            'breadcrumb'       => [['Beranda', route('dashboard')], ['Toko'], ['Promo', route($this->module.'.index')], ['Ubah Data']],
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
