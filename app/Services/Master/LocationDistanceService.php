<?php

namespace App\Services\Master;

use App\Models\LocationDistance;
use App\Models\LocationNode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LocationDistanceService
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return LocationDistance::query()
            ->with(['origin', 'destination'])
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->whereHas('origin', function ($q) use ($filters) {
                    $q->where('name', 'like', '%'.$filters['search'].'%');
                })->orWhereHas('destination', function ($q) use ($filters) {
                    $q->where('name', 'like', '%'.$filters['search'].'%');
                });
            })
            ->orderBy('origin_node_id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<string, string> id => name untuk select
     */
    public function nodeOptions(): array
    {
        return LocationNode::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function create(array $data): LocationDistance
    {
        [$originId, $destinationId] = $this->canonicalPair($data['origin_node_id'], $data['destination_node_id']);

        return LocationDistance::create([
            'origin_node_id' => $originId,
            'destination_node_id' => $destinationId,
            'distance_km' => $data['distance_km'],
        ]);
    }

    public function update(LocationDistance $locationDistance, array $data): bool
    {
        [$originId, $destinationId] = $this->canonicalPair($data['origin_node_id'], $data['destination_node_id']);

        return $locationDistance->update([
            'origin_node_id' => $originId,
            'destination_node_id' => $destinationId,
            'distance_km' => $data['distance_km'],
        ]);
    }

    public function delete(LocationDistance $locationDistance): ?bool
    {
        return $locationDistance->delete();
    }

    /**
     * Cek apakah pasangan node (dalam arah manapun) sudah tercatat.
     */
    public function pairExists(string $originNodeId, string $destinationNodeId, ?string $ignoreId = null): bool
    {
        [$originId, $destinationId] = $this->canonicalPair($originNodeId, $destinationNodeId);

        return LocationDistance::query()
            ->where('origin_node_id', $originId)
            ->where('destination_node_id', $destinationId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }

    /**
     * Normalisasi agar satu baris per pasangan (undirected).
     *
     * @return array{0: string, 1: string}
     */
    protected function canonicalPair(string $originNodeId, string $destinationNodeId): array
    {
        return $originNodeId <= $destinationNodeId
            ? [$originNodeId, $destinationNodeId]
            : [$destinationNodeId, $originNodeId];
    }
}
