<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\LocationNode;

class CustomerAddressService
{
    public function paginate(Customer $customer, array $filters = [], int $perPage = 10)
    {
        return CustomerAddress::query()
            ->where('customer_id', $customer->id)
            ->with('locationNode')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('full_address', 'like', "%{$search}%"))
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(Customer $customer, array $data): CustomerAddress
    {
        $isDefault = $data['is_default'] ?? false;

        if ($isDefault) {
            $this->clearDefault($customer);
        }

        if (! $customer->addresses()->exists()) {
            $isDefault = true;
        }

        return $customer->addresses()->create([
            ...$data,
            'is_default' => $isDefault,
        ]);
    }

    public function update(Customer $customer, CustomerAddress $address, array $data): CustomerAddress
    {
        abort_unless($address->customer_id === $customer->id, 403);

        $isDefault = $data['is_default'] ?? false;

        if ($isDefault) {
            $this->clearDefault($customer);
        }

        $address->update([...$data, 'is_default' => $isDefault]);

        return $address;
    }

    public function setDefault(Customer $customer, CustomerAddress $address): void
    {
        abort_unless($address->customer_id === $customer->id, 403);

        $this->clearDefault($customer);
        $address->update(['is_default' => true]);
    }

    public function delete(Customer $customer, CustomerAddress $address): void
    {
        abort_unless($address->customer_id === $customer->id, 403);
        $address->delete();
    }

    public function nodeOptions(): array
    {
        return LocationNode::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function clearDefault(Customer $customer): void
    {
        $customer->addresses()->update(['is_default' => false]);
    }
}
