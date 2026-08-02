<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\StorefrontService;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function __construct(protected StorefrontService $service) {}

    public function index(Request $request)
    {
        $stores = $this->service->stores(
            $request->only(['search']),
            (int) $request->input('per_page', 12)
        );

        return view('customer.storefront.index', compact('stores'));
    }

    public function show(string $store)
    {
        $store = $this->service->store($store);
        $products = $this->service->storeProducts($store, request('search'));

        return view('customer.storefront.store', compact('store', 'products'));
    }

    public function product(string $store, string $product)
    {
        $store = $this->service->store($store);
        $product = $this->service->product($store, $product);

        return view('customer.storefront.product', compact('store', 'product'));
    }
}
