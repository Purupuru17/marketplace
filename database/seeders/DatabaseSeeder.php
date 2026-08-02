<?php

namespace Database\Seeders;

use IdCore\CoreStarter\Database\Seeders\CoreDatabaseSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CoreDatabaseSeeder::class);
        $this->call(MasterDataSeeder::class);
        $this->call(LocationDataSeeder::class);
        $this->call(StoreDataSeeder::class);
        $this->call(CatalogDataSeeder::class);
    }
}
