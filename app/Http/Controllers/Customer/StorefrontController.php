<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\Customer\StorefrontService;
use App\Services\Shipping\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StorefrontController extends Controller
{
    public function __construct(protected StorefrontService $service) {}

    protected function customer()
    {
        return Auth::guard('customer')->user();
    }

    public function index(Request $request)
    {
        $data = $this->service->home($this->customer(), (int) $request->input('per_page', 12));

        $data['address_label'] = $this->addressLabel();

        return view('customer.storefront.index', $data);
    }

    public function search(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['uuid', 'exists:categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'radius' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'in:default,price_asc,price_desc,latest,sold'],
        ]);

        $filters['store_ids'] = $this->filterStoresByRadius((float) ($filters['radius'] ?? 0));

        $products = $this->service->search($filters, $this->customer());

        return view('customer.storefront.search', [
            'products' => $products,
            'categories' => $this->service->categories(),
            'filters' => $filters,
        ]);
    }

    public function show(string $store)
    {
        $data = $this->service->store($store, $this->customer());
        $products = $this->service->storeProducts(
            $data['store'],
            request('search'),
            ['category_id' => request('category_id'), 'sort' => request('sort', 'default')]
        );

        return view('customer.storefront.store', [
            'store' => $data['store'],
            'shipping' => $data['shipping'],
            'store_ratings' => $data['store_ratings'],
            'products' => $products,
            'categories' => $this->service->categories(),
            'address_label' => $this->addressLabel(),
        ]);
    }

    public function product(string $store, string $product)
    {
        $data = $this->service->product(
            $this->service->store($store, $this->customer())['store'],
            $product,
            $this->customer()
        );

        return view('customer.storefront.product', [
            'store' => $data['store'],
            'product' => $data['product'],
            'shipping' => $data['shipping'],
            'store_ratings' => $data['store_ratings'],
            'other_products' => $data['other_products'],
            'address_label' => $this->addressLabel(),
        ]);
    }

    protected function addressLabel(): ?string
    {
        $customer = $this->customer();
        if (! $customer) {
            return null;
        }

        $address = $customer->addresses()
            ->orderByDesc('is_default')
            ->first();

        if (! $address) {
            return null;
        }

        $label = $address->label ?: ($address->recipient_name ?? 'Alamat');

        return $label.' — '.$address->full_address;
    }

    protected function filterStoresByRadius(float $radius): array
    {
        if ($radius <= 0) {
            return [];
        }

        $destination = null;
        if ($customer = $this->customer()) {
            $destination = $customer->addresses()->with('locationNode')->orderByDesc('is_default')->first()?->locationNode;
        }

        if (! $destination) {
            return [];
        }

        return Store::query()
            ->where('status', 'active')
            ->whereNotNull('location_node_id')
            ->get()
            ->filter(function ($store) use ($destination, $radius) {
                $distance = app(ShippingService::class)
                    ->estimate($store, $destination)['distance_km'];

                return $distance !== null && $distance <= $radius;
            })
            ->pluck('id')
            ->all();
    }
}
