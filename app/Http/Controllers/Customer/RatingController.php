<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ProductRating;
use App\Services\Customer\RatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

        try {
            $this->service->create(
                Auth::guard('customer')->user(),
                $validated['order_item_id'],
                (int) $validated['rating'],
                $validated['review'] ?? null
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Terima kasih atas penilaian Anda.');
    }

    public function destroy(ProductRating $rating)
    {
        $customer = Auth::guard('customer')->user();

        abort_unless($rating->customer_id === $customer->id, 403);

        $rating->delete();

        return back()->with('success', 'Penilaian dihapus.');
    }
}
