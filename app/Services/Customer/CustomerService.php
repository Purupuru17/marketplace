<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\CustomerLevel;

class CustomerService
{
    public function register(array $data): Customer
    {
        $level = CustomerLevel::where('name', 'Bronze')->first();

        return Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'customer_level_id' => $level?->id,
            'status' => 'active',
        ]);
    }

    public function getByEmail(string $email): ?Customer
    {
        return Customer::where('email', $email)->first();
    }
}
