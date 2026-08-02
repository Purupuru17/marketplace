<?php

namespace Tests\Feature;

use App\Models\LocationDistance;
use App\Models\LocationNode;
use App\Services\Shipping\DijkstraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DijkstraServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_shortest_path_uses_intermediate_nodes(): void
    {
        [$jakarta, $bandung, $cirebon, $semarang, $surabaya] = $this->createGraph();

        $result = app(DijkstraService::class)->shortestPath($jakarta->id, $surabaya->id);

        $this->assertSame(810.0, $result['distance_km']);
        $this->assertSame([
            $jakarta->id,
            $bandung->id,
            $cirebon->id,
            $semarang->id,
            $surabaya->id,
        ], $result['node_ids']);
        $this->assertSame([
            'Kota Jakarta',
            'Kota Bandung',
            'Kota Cirebon',
            'Kota Semarang',
            'Kota Surabaya',
        ], $result['names']);
    }

    public function test_distance_is_symmetric_in_undirected_graph(): void
    {
        [$jakarta, , , , $surabaya] = $this->createGraph();

        $service = app(DijkstraService::class);

        $this->assertSame(
            $service->distanceBetween($jakarta->id, $surabaya->id),
            $service->distanceBetween($surabaya->id, $jakarta->id)
        );
    }

    public function test_unreachable_node_returns_null(): void
    {
        [$jakarta, , , , , $terpencil] = $this->createGraph();

        $this->assertNull(app(DijkstraService::class)->distanceBetween($jakarta->id, $terpencil->id));
    }

    public function test_same_node_distance_is_zero(): void
    {
        [$jakarta] = $this->createGraph();

        $result = app(DijkstraService::class)->shortestPath($jakarta->id, $jakarta->id);

        $this->assertSame(0.0, $result['distance_km']);
        $this->assertSame([$jakarta->id], $result['node_ids']);
    }

    /**
     * @return array<int, LocationNode>
     */
    private function createGraph(): array
    {
        $nodes = [];

        foreach (['Kota Jakarta', 'Kota Bandung', 'Kota Cirebon', 'Kota Semarang', 'Kota Surabaya', 'Kota Terpencil'] as $name) {
            $nodes[] = LocationNode::create(['name' => $name]);
        }

        [$jakarta, $bandung, $cirebon, $semarang, $surabaya] = $nodes;

        $edges = [
            [$jakarta, $bandung, 150],
            [$bandung, $cirebon, 130],
            [$cirebon, $semarang, 210],
            [$semarang, $surabaya, 320],
            [$jakarta, $surabaya, 900],
        ];

        foreach ($edges as [$origin, $destination, $distance]) {
            LocationDistance::create([
                'origin_node_id' => $origin->id,
                'destination_node_id' => $destination->id,
                'distance_km' => $distance,
            ]);
        }

        return $nodes;
    }
}
