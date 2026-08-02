<?php

namespace Tests\Concerns;

use Database\Seeders\CatalogDataSeeder;
use Database\Seeders\CustomerDataSeeder;
use Database\Seeders\LocationDataSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\StoreDataSeeder;
use IdCore\CoreStarter\Database\Seeders\CoreDatabaseSeeder;
use Illuminate\Support\Facades\Cache;

trait SeedsMarketplace
{
    protected function seedCore(): void
    {
        $this->seed(CoreDatabaseSeeder::class);
    }

    protected function seedMaster(): void
    {
        $this->seed(MasterDataSeeder::class);
    }

    protected function seedLocations(): void
    {
        $this->seed(LocationDataSeeder::class);
    }

    protected function seedStores(): void
    {
        $this->seed(StoreDataSeeder::class);
    }

    protected function seedCatalog(): void
    {
        $this->seed(CatalogDataSeeder::class);
    }

    protected function seedCustomers(): void
    {
        $this->seed(CustomerDataSeeder::class);
    }

    protected function seedMarketplace(): void
    {
        $this->seedCore();
        $this->seedMaster();
        $this->seedLocations();
        $this->seedStores();
        $this->seedCatalog();
        $this->seedCustomers();
    }

    protected function flushTestCache(): void
    {
        Cache::flush();
    }
}
