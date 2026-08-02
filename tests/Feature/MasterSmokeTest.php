<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use IdCore\CoreStarter\Database\Seeders\CoreDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoreDatabaseSeeder::class);
        $this->seed(MasterDataSeeder::class);
    }

    public function test_index_and_create_pages_render(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();

        $pages = [
            ['master.store-level.index', null, 'Store Level'],
            ['master.store-level.create', null, 'Tambah Store Level'],
            ['master.customer-level.index', null, 'Customer Level'],
            ['master.customer-level.create', null, 'Tambah Customer Level'],
            ['master.category.index', null, 'Kategori'],
            ['master.category.create', null, 'Tambah Kategori'],
            ['master.attribute.index', null, 'Atribut'],
            ['master.attribute.create', null, 'Tambah Atribut'],
        ];

        foreach ($pages as [$name, $param, $needle]) {
            $url = $param ? route($name, $param) : route($name);
            $this->actingAs($user)->get($url)
                ->assertOk()
                ->assertSee($needle, false);
        }
    }

    public function test_crud_store_level(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();

        $this->actingAs($user)
            ->post(route('master.store-level.store'), [
                'name' => 'Enterprise',
                'price' => 300000,
                'max_products' => 1000,
                'max_discount' => 80,
                'can_run_campaign' => 1,
                'sort_order' => 4,
                'status' => 'active',
            ])
            ->assertRedirect(route('master.store-level.index'));

        $this->assertDatabaseHas('store_levels', ['name' => 'Enterprise', 'can_run_campaign' => 1]);
    }

    public function test_crud_attribute_with_values(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();

        $this->actingAs($user)
            ->post(route('master.attribute.store'), [
                'name' => 'Bahan',
                'values' => ['Katun', 'Polyester', ''],
            ])
            ->assertRedirect(route('master.attribute.index'));

        $this->assertDatabaseHas('attribute_values', ['value' => 'Katun']);
        $this->assertDatabaseMissing('attribute_values', ['value' => '']);
    }

    public function test_crud_category_with_parent(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();
        $parent = Category::where('slug', 'elektronik')->firstOrFail();

        $this->actingAs($user)
            ->post(route('master.category.store'), [
                'parent_id' => $parent->id,
                'name' => 'Handphone',
                'sort_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('master.category.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Handphone', 'slug' => 'handphone', 'parent_id' => $parent->id]);
    }
}
