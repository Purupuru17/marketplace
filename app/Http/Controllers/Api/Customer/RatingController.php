<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\ProductRating;
use App\Services\Customer\RatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RatingController extends Controller
{
    public function __construct(protected RatingService $service) {}

    public function eligible(Request $request)
    {
        $customer = $request->user('api-customer');

        $items = OrderItem::query()
            ->whereHas('order', fn ($q) => $q
                ->where('status', 'completed')
                ->whereHas('invoice', fn ($i) => $i->where('customer_id', $customer->id)))
            ->doesntHave('rating')
            ->with(['product.store', 'product.images', 'variant.attributeValues.attribute'])
            ->latest('created_at')
            ->get();

        return response()->json([
            'data' => [
                'items' => $items->map(fn (OrderItem $item) => [
                    'order_item_id' => $item->id,
                    'qty' => $item->qty,
                    'product' => [
                        'name' => $item->product?->name,
                        'slug' => $item->product?->slug,
                        'store' => $item->product?->store?->store_name,
                        'image_url' => $item->product?->images?->sortBy('position')->first()
                            ? url(Storage::disk('public')->url($item->product->images->sortBy('position')->first()->path))
                            : null,
                    ],
                    'variant' => [
                        'sku' => $item->variant?->sku,
                        'attributes' => $item->variant?->attributeValues?->map(fn ($value) => [
                            'attribute' => $value->attribute?->name,
                            'value' => $value->value,
                        ])->values() ?? [],
                    ],
                ])->values(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_item_id' => ['required', 'uuid', 'exists:order_items,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        $rating = $this->service->create(
            $request->user('api-customer'),
            $validated['order_item_id'],
            (int) $validated['rating'],
            $validated['review'] ?? null,
        );

        return response()->json([
            'data' => [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'review' => $rating->review,
            ],
        ], 201);
    }

    public function destroy(Request $request, ProductRating $rating)
    {
        abort_unless($rating->customer_id === $request->user('api-customer')->id, 403);

        $rating->delete();

        return response()->json([
            'data' => ['message' => 'Rating dihapus.'],
        ]);
    }
}
