<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use IdCore\CoreStarter\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CatalogDataSeeder extends Seeder
{
    protected array $permissions = [
        'product' => ['index', 'create', 'edit', 'delete'],
        'product-variant' => ['index', 'create', 'edit', 'delete'],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seedPermissions();
        $this->seedOwnerRole();
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

    protected function seedOwnerRole(): void
    {
        $ownerRole = Role::firstOrCreate(['name' => 'Toko', 'guard_name' => 'web']);

        $catalogPermissions = collect($this->permissions)
            ->flatMap(fn (array $actions, string $module) => array_map(
                fn (string $action) => Permission::findByName("{$module}.{$action}", 'web'),
                $actions
            ))
            ->all();

        $ownerRole->syncPermissions($catalogPermissions);

        $owner = User::where('email', 'toko@gmail.com')->first();

        if ($owner) {
            $owner->assignRole($ownerRole);
            $owner->update(['default_role_id' => $ownerRole->id]);
        }
    }

    protected function seedSampleData(): void
    {
        $store = Store::where('store_code', 'STR-DEMO1')->first();

        if (! $store) {
            return;
        }

        $food = Category::where('name', 'Makanan & Minuman')->first();
        $foods = [
            ['name' => 'Nasi Goreng Spesial', 'sku' => 'NGS-REG', 'price' => 25000, 'stock' => 50, 'weight' => 350],
            ['name' => 'Ayam Geprek', 'sku' => 'AG-REG', 'price' => 22000, 'stock' => 40, 'weight' => 300],
        ];

        foreach ($foods as $item) {
            $product = Product::firstOrCreate(
                ['store_id' => $store->id, 'slug' => Str::slug($item['name'])],
                [
                    'category_id' => $food?->id,
                    'name' => $item['name'],
                    'description' => 'Produk contoh dari seeder.',
                    'status' => 'active',
                    'is_featured' => false,
                ]
            );

            ProductVariant::firstOrCreate(
                ['store_id' => $store->id, 'sku' => $item['sku']],
                [
                    'product_id' => $product->id,
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'weight_grams' => $item['weight'],
                    'status' => 'active',
                ]
            );
        }

        $spiciness = Attribute::firstOrCreate(['name' => 'Level Pedas']);
        $spicyValueIds = [];

        foreach (['Tidak Pedas', 'Sedang', 'Extra Pedas'] as $value) {
            $spicyValueIds[$value] = $spiciness->values()->firstOrCreate(['value' => $value])->id;
        }

        $ayamGeprek = ProductVariant::where('store_id', $store->id)->where('sku', 'AG-REG')->first();

        if ($ayamGeprek) {
            $ayamGeprek->attributeValues()->sync([$spicyValueIds['Sedang']]);
        }
    }

    protected function seedMenus(): void
    {
        $menuKatalog = Menu::firstOrCreate(
            ['name' => 'Katalog', 'parent_id' => null],
            ['url' => '#', 'icon' => 'heroicon-o-shopping-bag', 'sort_by' => 4]
        );

        $childMenus = [
            ['name' => 'Produk', 'url' => '/katalog/product', 'sort_by' => 1, 'icon' => 'heroicon-o-shopping-bag', 'actions' => $this->permissions['product']],
            ['name' => 'Varian Produk', 'url' => '/katalog/product-variant', 'sort_by' => 2, 'icon' => 'heroicon-o-cube', 'actions' => $this->permissions['product-variant']],
        ];

        foreach ($childMenus as $menu) {
            Menu::firstOrCreate(
                ['name' => $menu['name'], 'parent_id' => $menuKatalog->id],
                $menu
            );
        }
    }
}
