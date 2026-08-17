<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Services\Customer\CustomerAddressService;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(protected CustomerAddressService $service) {}

    public function index(Request $request)
    {
        $addresses = $this->service->paginate(
            $request->user('api-customer'),
            $request->only(['search']),
        );

        return response()->json([
            'data' => [
                'items' => $addresses->map(fn (CustomerAddress $address) => $this->payload($address))->values(),
                'pagination' => [
                    'current_page' => $addresses->currentPage(),
                    'last_page' => $addresses->lastPage(),
                    'total' => $addresses->total(),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $address = $this->service->create(
            $request->user('api-customer'),
            $this->validateAddress($request),
        );

        return response()->json(['data' => $this->payload($address)], 201);
    }

    public function update(Request $request, CustomerAddress $address)
    {
        $address = $this->service->update(
            $request->user('api-customer'),
            $address,
            $this->validateAddress($request),
        );

        return response()->json(['data' => $this->payload($address)]);
    }

    public function setDefault(Request $request, CustomerAddress $address)
    {
        $this->service->setDefault($request->user('api-customer'), $address);

        return response()->json(['data' => ['message' => 'Alamat utama diperbarui.']]);
    }

    public function destroy(Request $request, CustomerAddress $address)
    {
        $this->service->delete($request->user('api-customer'), $address);

        return response()->json(['data' => ['message' => 'Alamat dihapus.']]);
    }

    protected function payload(CustomerAddress $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label,
            'recipient_name' => $address->recipient_name,
            'phone' => $address->phone,
            'full_address' => $address->full_address,
            'location_node_id' => $address->location_node_id,
            'is_default' => (bool) $address->is_default,
        ];
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
