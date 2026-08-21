<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ProductRating;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function destroy(ProductRating $rating)
    {
        $customer = Auth::guard('customer')->user();

        abort_unless($rating->customer_id === $customer->id, 403);

        $rating->delete();

        return back()->with('success', 'Penilaian dihapus.');
    }
}
