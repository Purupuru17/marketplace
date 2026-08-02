<?php

namespace App\Http\Controllers\Store;

use App\Models\Promotion;
use App\Services\Store\PromotionService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class PromotionController extends BaseCoreController
{
    public function __construct(protected PromotionService $service) {}

    public function index(Request $request)
    {
        $promotions = $this->service->paginate(
            auth()->user(),
            $request->only(['search', 'status']),
            (int) $request->input('per_page', 10)
        );

        return view('store.promotion.index', compact('promotions'));
    }

    public function create()
    {
        return view('store.promotion.form', [
            'promotion' => null,
            'storeOptions' => $this->service->storeOptions(auth()->user()),
            'productOptions' => $this->service->productOptions(auth()->user()),
            'selectedProducts' => collect(),
            'isAdmin' => auth()->user()->hasRole('Administrator'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePromotion($request);

        $this->service->create(auth()->user(), $validated, $request->input('products', []));

        return redirect()
            ->route('toko.promotion.index')
            ->with('success', 'Promo berhasil ditambahkan.');
    }

    public function edit(Promotion $promotion)
    {
        $this->service->authorize(auth()->user(), $promotion);

        $promotion->load('products');

        return view('store.promotion.form', [
            'promotion' => $promotion,
            'storeOptions' => $this->service->storeOptions(auth()->user()),
            'productOptions' => $this->service->productOptions(auth()->user()),
            'selectedProducts' => $promotion->products->pluck('id'),
            'isAdmin' => auth()->user()->hasRole('Administrator'),
        ]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $this->validatePromotion($request);

        $this->service->update(auth()->user(), $promotion, $validated, $request->input('products', []));

        return redirect()
            ->route('toko.promotion.index')
            ->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(Promotion $promotion)
    {
        $this->service->delete(auth()->user(), $promotion);

        return redirect()
            ->route('toko.promotion.index')
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
