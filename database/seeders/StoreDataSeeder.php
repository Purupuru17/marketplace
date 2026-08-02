<?php

namespace Database\Seeders;

use App\Models\LocationNode;
use App\Models\Store;
use App\Models\StoreLevel;
use App\Models\StoreOperatingHour;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Services\Store\StoreService;
use IdCore\CoreStarter\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StoreDataSeeder extends Seeder
{
    protected array $permissions = [
        'store' => ['index', 'create', 'edit', 'delete'],
        'subscription' => ['index', 'create', 'edit', 'delete'],
        'subscription-invoice' => ['index', 'create', 'edit', 'delete'],
        'orders' => ['index', 'detail', 'edit'],
        'wallet' => ['index', 'create'],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seedPermissions();
        $this->seedSampleData();
        $this->seedMenus();
    }

    protected function seedPermissions(): void
    {
        foreach ($this->permissions as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        $administrator = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $administrator->syncPermissions(Permission::all());
    }

    protected function seedSampleData(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'toko@gmail.com'],
            [
                'name' => 'Pemilik Toko',
                'password' => Hash::make('12345'),
                'email_verified_at' => now(),
            ]
        );

        $basic = StoreLevel::firstOrCreate(['name' => 'Basic']);
        $jakarta = LocationNode::firstOrCreate(['name' => 'Kota Jakarta']);

        $store = Store::firstOrCreate(
            ['store_code' => 'STR-DEMO1'],
            [
                'user_id' => $owner->id,
                'store_level_id' => $basic->id,
                'location_node_id' => $jakarta->id,
                'store_name' => 'Toko Berkah',
                'slug' => 'toko-berkah',
                'description' => 'Toko contoh untuk pengembangan.',
                'phone' => '081234567890',
                'email' => 'toko@berkah.com',
                'rate_per_km' => 2000,
                'min_free_distance_km' => 3,
                'max_radius_km' => 25,
                'status' => 'active',
            ]
        );

        foreach (StoreService::DAYS as $day) {
            StoreOperatingHour::updateOrCreate(
                ['store_id' => $store->id, 'day' => $day],
                ['is_open' => ! in_array($day, ['sunday']), 'opens_at' => '08:00', 'closes_at' => '20:00']
            );
        }

        $subscription = Subscription::firstOrCreate(
            ['store_id' => $store->id, 'starts_at' => now()->startOfMonth()->toDateString()],
            [
                'store_level_id' => $basic->id,
                'ends_at' => now()->endOfMonth()->toDateString(),
                'status' => 'active',
                'auto_renew' => true,
            ]
        );

        SubscriptionInvoice::firstOrCreate(
            ['subscription_id' => $subscription->id, 'invoice_no' => 'INV-DEMO-001'],
            [
                'amount' => $basic->price,
                'status' => 'pending',
                'due_at' => now()->startOfMonth()->toDateString(),
            ]
        );
    }

    protected function seedMenus(): void
    {
        $menuToko = Menu::firstOrCreate(
            ['name' => 'Toko', 'parent_id' => null],
            ['url' => '#', 'icon' => 'heroicon-o-building-storefront', 'sort_by' => 3]
        );

        $childMenus = [
            ['name' => 'Toko', 'url' => '/toko/store', 'sort_by' => 1, 'icon' => 'heroicon-o-building-storefront', 'actions' => $this->permissions['store']],
            ['name' => 'Subscription', 'url' => '/toko/subscription', 'sort_by' => 2, 'icon' => 'heroicon-o-credit-card', 'actions' => $this->permissions['subscription']],
            ['name' => 'Invoice Subscription', 'url' => '/toko/subscription-invoice', 'sort_by' => 3, 'icon' => 'heroicon-o-document-text', 'actions' => $this->permissions['subscription-invoice']],
            ['name' => 'Pesanan', 'url' => '/toko/orders', 'sort_by' => 4, 'icon' => 'heroicon-o-truck', 'actions' => $this->permissions['orders']],
            ['name' => 'Saldo', 'url' => '/toko/wallet', 'sort_by' => 5, 'icon' => 'heroicon-o-wallet', 'actions' => $this->permissions['wallet']],
        ];

        foreach ($childMenus as $menu) {
            Menu::firstOrCreate(
                ['name' => $menu['name'], 'parent_id' => $menuToko->id],
                $menu
            );
        }
    }
}
