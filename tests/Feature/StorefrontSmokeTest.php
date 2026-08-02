<?php

namespace Tests\Feature;

use App\Models\Store;
use Database\Seeders\CatalogDataSeeder;
use Database\Seeders\LocationDataSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\StoreDataSeeder;
use IdCore\CoreStarter\Database\Seeders\CoreDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CoreDatabaseSeeder::class);
        $this->seed(MasterDataSeeder::class);
        $this->seed(LocationDataSeeder::class);
        $this->seed(StoreDataSeeder::class);
        $this->seed(CatalogDataSeeder::class);
    }

    public function test_public_pages_render(): void
    {
        $this->get(route('storefront.index'))
            ->assertOk()
            ->assertSee('Toko Berkah', false);

        $this->get(route('storefront.store', 'toko-berkah'))
            ->assertOk()
            ->assertSee('Nasi Goreng Spesial', false)
            ->assertSee('Ayam Geprek', false);

        $this->get(route('storefront.product', ['toko-berkah', 'nasi-goreng-spesial']))
            ->assertOk()
            ->assertSee('NGS-REG', false)
            ->assertSee('Rp 25.000', false);
    }

    public function test_product_page_shows_attributes(): void
    {
        $this->get(route('storefront.product', ['toko-berkah', 'ayam-geprek']))
            ->assertOk()
            ->assertSee('AG-REG', false)
            ->assertSee('Sedang', false);
    }

    public function test_add_to_cart_requires_login(): void
    {
        $this->get(route('storefront.product', ['toko-berkah', 'nasi-goreng-spesial']))
            ->assertOk()
            ->assertSee('Masuk untuk membeli', false);
    }

    public function test_inactive_store_is_not_listed(): void
    {
        Store::where('slug', 'toko-berkah')->update(['status' => 'inactive']);

        $this->get(route('storefront.index'))
            ->assertOk()
            ->assertDontSee('Toko Berkah', false);

        $this->get(route('storefront.store', 'toko-berkah'))->assertNotFound();
    }
}
