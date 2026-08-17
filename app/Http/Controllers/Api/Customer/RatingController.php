<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\ProductRating;
use App\Services\Customer\RatingService;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function __construct(protected RatingService $service) {}

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
