<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\LoyaltyService;
use Illuminate\Support\Facades\Auth;

class PointController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        return view('customer.point.index', [
            'customer' => $customer,
            'availablePoints' => app(LoyaltyService::class)->availablePoints($customer),
            'transactions' => $customer->pointTransactions()
                ->latest('created_at')
                ->paginate(15),
        ]);
    }
}
