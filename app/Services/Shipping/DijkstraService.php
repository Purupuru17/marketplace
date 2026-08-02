<?php

namespace App\Services\Shipping;

use App\Models\LocationDistance;
use App\Models\LocationNode;
use SplPriorityQueue;

class DijkstraService
{
    /**
     * Mencari jalur terpendek (Dijkstra) antara dua node lokasi.
     * Grafik bersifat undirected: satu baris per pasangan, arah balik dibangun di memori.
     *
     * @return array{distance_km: float|null, node_ids: array<int, string>, names: array<int, string|null>}
     */
    public function shortestPath(string $originId, string $destinationId): array
    {
        $empty = ['distance_km' => null, 'node_ids' => [], 'names' => []];

        if ($originId === $destinationId) {
            $nodes = $this->nodeNames([$originId]);

            return [
                'distance_km' => 0.0,
                'node_ids' => [$originId],
                'names' => array_map(fn (string $id) => $nodes[$id] ?? null, [$originId]),
            ];
        }

        $graph = $this->buildGraph();

        if (! isset($graph[$originId]) || ! isset($graph[$destinationId])) {
            return $empty;
        }

        $queue = new SplPriorityQueue;
        $queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);

        $distances = [$originId => 0.0];
        $previous = [];
        $visited = [];

        $queue->insert($originId, 0.0);

        while (! $queue->isEmpty()) {
            $current = $queue->extract();
            $nodeId = $current['data'];

            if (isset($visited[$nodeId])) {
                continue;
            }

            $visited[$nodeId] = true;

            if ($nodeId === $destinationId) {
                break;
            }

            foreach ($graph[$nodeId] as $neighbor => $weight) {
                $newDistance = $distances[$nodeId] + $weight;

                if (! isset($distances[$neighbor]) || $newDistance < $distances[$neighbor]) {
                    $distances[$neighbor] = $newDistance;
                    $previous[$neighbor] = $nodeId;
                    $queue->insert($neighbor, -$newDistance);
                }
            }
        }

        if (! isset($distances[$destinationId])) {
            return $empty;
        }

        $path = [];

        for ($nodeId = $destinationId; $nodeId !== null; $nodeId = $previous[$nodeId] ?? null) {
            $path[] = $nodeId;

            if ($nodeId === $originId) {
                break;
            }
        }

        $path = array_reverse($path);
        $nodes = $this->nodeNames($path);

        return [
            'distance_km' => round($distances[$destinationId], 2),
            'node_ids' => $path,
            'names' => array_map(fn (string $id) => $nodes[$id] ?? null, $path),
        ];
    }

    /**
     * Jarak terpendek antar dua node, atau null bila tidak terhubung.
     */
    public function distanceBetween(string $originId, string $destinationId): ?float
    {
        return $this->shortestPath($originId, $destinationId)['distance_km'];
    }

    /**
     * Membangun adjacency list undirected dari tabel location_distances.
     *
     * @return array<string, array<string, float>>
     */
    protected function buildGraph(): array
    {
        $graph = [];

        LocationDistance::query()
            ->select(['origin_node_id', 'destination_node_id', 'distance_km'])
            ->chunk(500, function ($distances) use (&$graph) {
                foreach ($distances as $distance) {
                    $weight = (float) $distance->distance_km;
                    $graph[$distance->origin_node_id][$distance->destination_node_id] = $weight;
                    $graph[$distance->destination_node_id][$distance->origin_node_id] = $weight;
                }
            });

        return $graph;
    }

    /**
     * @param  array<int, string>  $nodeIds
     * @return array<string, string>
     */
    protected function nodeNames(array $nodeIds): array
    {
        return LocationNode::query()
            ->whereIn('id', $nodeIds)
            ->pluck('name', 'id')
            ->all();
    }
}
