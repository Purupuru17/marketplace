<?php

namespace Tests\Feature;

use App\Models\LocationDistance;
use App\Models\LocationNode;
use App\Models\User;
use Database\Seeders\LocationDataSeeder;
use Database\Seeders\MasterDataSeeder;
use IdCore\CoreStarter\Database\Seeders\CoreDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CoreDatabaseSeeder::class);
        $this->seed(MasterDataSeeder::class);
        $this->seed(LocationDataSeeder::class);
    }

    public function test_index_and_create_pages_render(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();

        $pages = [
            ['master.location-node.index', null, 'Node Lokasi'],
            ['master.location-node.create', null, 'Tambah Node Lokasi'],
            ['master.location-distance.index', null, 'Jarak Antar Node'],
            ['master.location-distance.create', null, 'Tambah Jarak Antar Node'],
        ];

        foreach ($pages as [$name, $param, $needle]) {
            $url = $param ? route($name, $param) : route($name);

            $this->actingAs($user)->get($url)
                ->assertOk()
                ->assertSee($needle, false);
        }
    }

    public function test_crud_location_node(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();

        $this->actingAs($user)
            ->post(route('master.location-node.store'), [
                'name' => 'Kota Medan',
                'lat' => '3.59',
                'lng' => '98.67',
                'status' => 'active',
            ])
            ->assertRedirect(route('master.location-node.index'));

        $this->assertDatabaseHas('location_nodes', ['name' => 'Kota Medan', 'lat' => '3.59', 'lng' => '98.67']);
    }

    public function test_crud_location_distance(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();
        $a = LocationNode::create(['name' => 'Kota A']);
        $b = LocationNode::create(['name' => 'Kota B']);

        $this->actingAs($user)
            ->post(route('master.location-distance.store'), [
                'origin_node_id' => $a->id,
                'destination_node_id' => $b->id,
                'distance_km' => 12.5,
            ])
            ->assertRedirect(route('master.location-distance.index'));

        $this->assertDatabaseHas('location_distances', [
            'origin_node_id' => $a->id,
            'destination_node_id' => $b->id,
            'distance_km' => '12.50',
        ]);
    }

    public function test_rejects_self_pair(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();
        $a = LocationNode::create(['name' => 'Kota A']);

        $this->actingAs($user)
            ->from(route('master.location-distance.create'))
            ->post(route('master.location-distance.store'), [
                'origin_node_id' => $a->id,
                'destination_node_id' => $a->id,
                'distance_km' => 5,
            ])
            ->assertSessionHasErrors('destination_node_id');

        $this->assertDatabaseMissing('location_distances', [
            'origin_node_id' => $a->id,
            'destination_node_id' => $a->id,
        ]);
    }

    public function test_rejects_reverse_duplicate(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();
        $a = LocationNode::create(['name' => 'Kota A']);
        $b = LocationNode::create(['name' => 'Kota B']);

        LocationDistance::create([
            'origin_node_id' => $a->id,
            'destination_node_id' => $b->id,
            'distance_km' => 10,
        ]);

        $this->actingAs($user)
            ->from(route('master.location-distance.create'))
            ->post(route('master.location-distance.store'), [
                'origin_node_id' => $b->id,
                'destination_node_id' => $a->id,
                'distance_km' => 10,
            ])
            ->assertSessionHasErrors('destination_node_id');

        $this->assertSame(1, LocationDistance::query()
            ->where('origin_node_id', $a->id)
            ->where('destination_node_id', $b->id)
            ->count());
    }
}
