<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class CatalogSmokeTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushTestCache();
        $this->seedCore();
        $this->seedMaster();
        $this->seedLocations();
        $this->seedStores();
        $this->seedCatalog();
    }

    public function test_index_and_create_pages_render(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();

        $pages = [
            ['katalog.product.index', null, 'Data Produk'],
            ['katalog.product.create', null, 'Tambah Produk'],
            ['katalog.product-variant.index', null, 'Data Varian Produk'],
            ['katalog.product-variant.create', null, 'Tambah Varian'],
        ];

        foreach ($pages as [$name, $param, $needle]) {
            $url = $param ? route($name, $param) : route($name);

            $this->actingAs($user)->get($url)
                ->assertOk()
                ->assertSee($needle, false);
        }
    }

    public function test_admin_crud_product_and_variant(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();
        $store = Store::where('store_code', 'STR-DEMO1')->firstOrFail();
        $category = Category::where('name', 'Makanan & Minuman')->firstOrFail();

        $this->actingAs($user)
            ->post(route('katalog.product.store'), [
                'store_id' => $store->id,
                'category_id' => $category->id,
                'name' => 'Es Teh Manis',
                'description' => 'Minuman segar.',
                'status' => 'active',
                'is_featured' => 1,
            ])
            ->assertRedirect(route('katalog.product.index'));

        $product = Product::where('slug', 'es-teh-manis')->firstOrFail();

        $this->assertSame($store->id, $product->store_id);
        $this->assertSame('active', $product->status);
        $this->assertTrue($product->is_featured);

        $this->actingAs($user)
            ->post(route('katalog.product-variant.store'), [
                'product_id' => $product->id,
                'sku' => 'ETM-REG',
                'price' => 5000,
                'stock' => 100,
                'weight_grams' => 200,
                'status' => 'active',
            ])
            ->assertRedirect(route('katalog.product-variant.index'));

        $variant = ProductVariant::where('sku', 'ETM-REG')->firstOrFail();

        $this->assertSame($store->id, $variant->store_id);
        $this->assertSame($product->id, $variant->product_id);

        $this->actingAs($user)
            ->put(route('katalog.product-variant.update', $variant->id), [
                'product_id' => $product->id,
                'sku' => 'ETM-REG',
                'price' => 6000,
                'stock' => 90,
                'weight_grams' => 200,
                'status' => 'inactive',
            ])
            ->assertRedirect(route('katalog.product-variant.index'));

        $this->assertSame('6000.00', (string) $variant->refresh()->price);
        $this->assertSame('inactive', $variant->status);

        $this->actingAs($user)
            ->delete(route('katalog.product-variant.destroy', $variant->id))
            ->assertRedirect(route('katalog.product-variant.index'));

        $this->actingAs($user)
            ->delete(route('katalog.product.destroy', $product->id))
            ->assertRedirect(route('katalog.product.index'));

        $this->assertNotNull($product->refresh()->deleted_at);
    }

    public function test_variant_with_attributes_sync(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();
        $product = Product::where('slug', 'nasi-goreng-spesial')->firstOrFail();
        $warna = Attribute::where('name', 'Warna')->firstOrFail();
        $ukuran = Attribute::where('name', 'Ukuran')->firstOrFail();
        $merah = $warna->values()->where('value', 'Merah')->firstOrFail();
        $ukuranL = $ukuran->values()->where('value', 'L')->firstOrFail();

        $this->actingAs($user)
            ->post(route('katalog.product-variant.store'), [
                'product_id' => $product->id,
                'sku' => 'NGS-ATTR-1',
                'price' => 28000,
                'stock' => 10,
                'weight_grams' => 350,
                'status' => 'active',
                'attribute_value_ids' => [$merah->id, $ukuranL->id],
            ])
            ->assertRedirect(route('katalog.product-variant.index'));

        $variant = ProductVariant::where('sku', 'NGS-ATTR-1')->firstOrFail();

        $this->assertSame(2, $variant->attributeValues()->count());

        $this->actingAs($user)->get(route('katalog.product-variant.index'))
            ->assertOk()
            ->assertSee('L · Merah', false);

        $this->actingAs($user)
            ->put(route('katalog.product-variant.update', $variant->id), [
                'product_id' => $product->id,
                'sku' => 'NGS-ATTR-1',
                'price' => 29000,
                'stock' => 8,
                'weight_grams' => 350,
                'status' => 'active',
                'attribute_value_ids' => [$ukuranL->id],
            ])
            ->assertRedirect(route('katalog.product-variant.index'));

        $this->assertSame(1, $variant->refresh()->attributeValues()->count());
        $this->assertSame('29000.00', (string) $variant->price);
    }

    public function test_variant_rejects_duplicate_attribute(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();
        $product = Product::where('slug', 'nasi-goreng-spesial')->firstOrFail();
        $ukuran = Attribute::where('name', 'Ukuran')->firstOrFail();
        $ukuranL = $ukuran->values()->where('value', 'L')->firstOrFail();
        $ukuranXL = $ukuran->values()->where('value', 'XL')->firstOrFail();

        $this->actingAs($user)
            ->post(route('katalog.product-variant.store'), [
                'product_id' => $product->id,
                'sku' => 'NGS-DUP-1',
                'price' => 28000,
                'stock' => 10,
                'weight_grams' => 350,
                'status' => 'active',
                'attribute_value_ids' => [$ukuranL->id, $ukuranXL->id],
            ])
            ->assertSessionHasErrors('attribute_value_ids');

        $this->assertSame(0, ProductVariant::where('sku', 'NGS-DUP-1')->count());
    }

    public function test_slug_is_unique_per_store(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();
        $storeA = Store::where('store_code', 'STR-DEMO1')->firstOrFail();
        $storeB = $this->createStore($user, 'Toko Kedua');

        $this->actingAs($user)->post(route('katalog.product.store'), [
            'store_id' => $storeA->id,
            'name' => 'Kopi Susu',
            'status' => 'active',
        ]);
        $this->actingAs($user)->post(route('katalog.product.store'), [
            'store_id' => $storeB->id,
            'name' => 'Kopi Susu',
            'status' => 'active',
        ]);

        $this->assertSame(1, Product::where('store_id', $storeA->id)->where('slug', 'kopi-susu')->count());
        $this->assertSame(1, Product::where('store_id', $storeB->id)->where('slug', 'kopi-susu')->count());

        $this->actingAs($user)->post(route('katalog.product.store'), [
            'store_id' => $storeA->id,
            'name' => 'Kopi Susu',
            'status' => 'active',
        ]);

        $this->assertSame(2, Product::where('store_id', $storeA->id)->where('slug', 'like', 'kopi-susu%')->count());
    }

    public function test_owner_sees_only_own_store_products(): void
    {
        $super = User::where('email', 'super@gmail.com')->firstOrFail();
        $owner = User::where('email', 'toko@gmail.com')->firstOrFail();
        $storeA = Store::where('store_code', 'STR-DEMO1')->firstOrFail();
        $storeB = $this->createStore($super, 'Toko Lain');

        $this->actingAs($super)->post(route('katalog.product.store'), [
            'store_id' => $storeB->id,
            'name' => 'Produk Toko B',
            'status' => 'active',
        ]);

        $response = $this->actingAs($owner)->get(route('katalog.product.index'));

        $response->assertOk()->assertSee('Nasi Goreng Spesial', false)->assertDontSee('Produk Toko B', false);
    }

    public function test_owner_cannot_access_other_store_product(): void
    {
        $super = User::where('email', 'super@gmail.com')->firstOrFail();
        $owner = User::where('email', 'toko@gmail.com')->firstOrFail();
        $storeB = $this->createStore($super, 'Toko Rahasia');

        $this->actingAs($super)->post(route('katalog.product.store'), [
            'store_id' => $storeB->id,
            'name' => 'Produk Rahasia',
            'status' => 'active',
        ]);

        $productB = Product::where('slug', 'produk-rahasia')->firstOrFail();

        $this->actingAs($owner)->get(route('katalog.product.edit', $productB->id))->assertForbidden();
        $this->actingAs($owner)->delete(route('katalog.product.destroy', $productB->id))->assertForbidden();
    }

    public function test_owner_store_select_is_scoped(): void
    {
        $super = User::where('email', 'super@gmail.com')->firstOrFail();
        $owner = User::where('email', 'toko@gmail.com')->firstOrFail();
        $this->createStore($super, 'Toko Super');

        $this->actingAs($owner)->get(route('katalog.product.create'))
            ->assertOk()
            ->assertDontSee('Toko Super', false);
    }

    protected function createStore(User $user, string $name): Store
    {
        $store = Store::create([
            'user_id' => $user->id,
            'store_code' => 'STR-'.strtoupper(Str::random(6)),
            'store_name' => $name,
            'slug' => Str::slug($name),
            'status' => 'active',
        ]);

        return $store;
    }
}
