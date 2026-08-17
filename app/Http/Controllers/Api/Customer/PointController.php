<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\LoyaltyService;
use Illuminate\Http\Request;

class PointController extends Controller
{
    public function __construct(protected LoyaltyService $service) {}

    public function index(Request $request)
    {
        $customer = $request->user('api-customer');

        $transactions = $customer->pointTransactions()
            ->latest('created_at')
            ->paginate(15);

        return response()->json([
            'data' => [
                'available_points' => $this->service->availablePoints($customer),
                'items' => $transactions->map(fn ($tx) => [
                    'type' => $tx->type,
                    'points' => $tx->points,
                    'description' => $tx->description,
                    'created_at' => $tx->created_at?->toIso8601String(),
                ])->values(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'total' => $transactions->total(),
                ],
            ],
        ]);
    }
}
