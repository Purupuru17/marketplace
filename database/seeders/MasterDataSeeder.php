<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\CustomerLevel;
use App\Models\StoreLevel;
use IdCore\CoreStarter\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MasterDataSeeder extends Seeder
{
    protected array $permissions = [
        'store-level' => ['index', 'create', 'edit', 'delete'],
        'customer-level' => ['index', 'create', 'edit', 'delete'],
        'category' => ['index', 'create', 'edit', 'delete'],
        'attribute' => ['index', 'create', 'edit', 'delete'],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seedPermissions();
        $this->seedMasterData();
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

    protected function seedMasterData(): void
    {
        StoreLevel::updateOrCreate(['name' => 'Free'], [
            'price' => 0,
            'max_products' => 10,
            'max_discount' => 5,
            'can_run_campaign' => false,
            'sort_order' => 1,
            'status' => 'active',
        ]);

        StoreLevel::updateOrCreate(['name' => 'Basic'], [
            'price' => 50000,
            'max_products' => 100,
            'max_discount' => 20,
            'can_run_campaign' => false,
            'sort_order' => 2,
            'status' => 'active',
        ]);

        StoreLevel::updateOrCreate(['name' => 'Premium'], [
            'price' => 150000,
            'max_products' => null,
            'max_discount' => 50,
            'can_run_campaign' => true,
            'sort_order' => 3,
            'status' => 'active',
        ]);

        CustomerLevel::updateOrCreate(['name' => 'Bronze'], [
            'minimum_points' => 0,
            'sort_order' => 1,
            'benefit' => 'Kumpulkan poin di setiap transaksi.',
            'status' => 'active',
        ]);

        CustomerLevel::updateOrCreate(['name' => 'Silver'], [
            'minimum_points' => 500,
            'sort_order' => 2,
            'benefit' => 'Diskon 2% + kumpulkan poin lebih cepat.',
            'status' => 'active',
        ]);

        CustomerLevel::updateOrCreate(['name' => 'Gold'], [
            'minimum_points' => 1500,
            'sort_order' => 3,
            'benefit' => 'Diskon 5% + gratis ongkir + prioritas layanan.',
            'status' => 'active',
        ]);

        $this->seedAttribute('Warna', ['Merah', 'Biru', 'Hitam']);
        $this->seedAttribute('Ukuran', ['S', 'M', 'L', 'XL']);

        Category::updateOrCreate(['name' => 'Elektronik'], ['slug' => 'elektronik', 'sort_order' => 1, 'status' => 'active']);
        Category::updateOrCreate(['name' => 'Fashion'], ['slug' => 'fashion', 'sort_order' => 2, 'status' => 'active']);
        Category::updateOrCreate(['name' => 'Makanan & Minuman'], ['slug' => 'makanan-minuman', 'sort_order' => 3, 'status' => 'active']);
    }

    protected function seedAttribute(string $name, array $values): void
    {
        $attribute = Attribute::updateOrCreate(['name' => $name]);

        foreach ($values as $value) {
            $attribute->values()->firstOrCreate(['value' => $value]);
        }
    }

    protected function seedMenus(): void
    {
        $menuMaster = Menu::firstOrCreate(
            ['name' => 'Master Data', 'parent_id' => null],
            ['url' => '#', 'icon' => 'heroicon-o-book-open', 'sort_by' => 2]
        );

        $childMenus = [
            ['name' => 'Store Level', 'url' => '/master/store-level', 'sort_by' => 1, 'icon' => 'heroicon-o-building-storefront', 'actions' => $this->permissions['store-level']],
            ['name' => 'Customer Level', 'url' => '/master/customer-level', 'sort_by' => 2, 'icon' => 'heroicon-o-user-group', 'actions' => $this->permissions['customer-level']],
            ['name' => 'Kategori', 'url' => '/master/category', 'sort_by' => 3, 'icon' => 'heroicon-o-tag', 'actions' => $this->permissions['category']],
            ['name' => 'Atribut', 'url' => '/master/attribute', 'sort_by' => 4, 'icon' => 'heroicon-o-swatch', 'actions' => $this->permissions['attribute']],
        ];

        foreach ($childMenus as $menu) {
            Menu::firstOrCreate(
                ['name' => $menu['name'], 'parent_id' => $menuMaster->id],
                $menu
            );
        }
    }
}
