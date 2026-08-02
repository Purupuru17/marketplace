<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Services\Customer\CustomerAddressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function __construct(protected CustomerAddressService $service) {}

    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $addresses = $this->service->paginate($customer, $request->only(['search']));

        return view('customer.address.index', compact('addresses'));
    }

    public function create()
    {
        return view('customer.address.form', [
            'address' => null,
            'nodeOptions' => $this->service->nodeOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAddress($request);

        $this->service->create(Auth::guard('customer')->user(), $validated);

        return redirect()->route('customer.address.index')
            ->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function edit(CustomerAddress $address)
    {
        $customer = Auth::guard('customer')->user();

        abort_unless($address->customer_id === $customer->id, 403);

        return view('customer.address.form', [
            'address' => $address,
            'nodeOptions' => $this->service->nodeOptions(),
        ]);
    }

    public function update(Request $request, CustomerAddress $address)
    {
        $validated = $this->validateAddress($request);

        $this->service->update(Auth::guard('customer')->user(), $address, $validated);

        return redirect()->route('customer.address.index')
            ->with('success', 'Alamat berhasil diperbarui.');
    }

    public function setDefault(CustomerAddress $address)
    {
        $this->service->setDefault(Auth::guard('customer')->user(), $address);

        return redirect()->route('customer.address.index')
            ->with('success', 'Alamat utama diperbarui.');
    }

    public function destroy(CustomerAddress $address)
    {
        $this->service->delete(Auth::guard('customer')->user(), $address);

        return redirect()->route('customer.address.index')
            ->with('success', 'Alamat berhasil dihapus.');
    }

    protected function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'full_address' => ['required', 'string'],
            'location_node_id' => ['nullable', 'uuid', 'exists:location_nodes,id'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }
}
