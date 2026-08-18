<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerDataSeeder extends Seeder
{
    public function run(): void
    {
        $bronze = CustomerLevel::where('name', 'Bronze')->first();

        Customer::firstOrCreate(
            ['email' => 'dina@gmail.com'],
            [
                'name' => 'Dina Puspita',
                'password' => Hash::make('12345'),
                'phone' => '081234567891',
                'customer_level_id' => $bronze?->id,
                'status' => 'active',
            ]
        );
    }
}
