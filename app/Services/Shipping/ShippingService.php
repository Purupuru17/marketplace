<?php

namespace App\Services\Shipping;

use App\Models\LocationNode;
use App\Models\Store;

class ShippingService
{
    public function __construct(protected DijkstraService $dijkstra) {}

    public function cost(Store $store, ?float $distanceKm): float
    {
        if ($distanceKm === null) {
            return 0.0;
        }

        $freeDistance = (float) $store->min_free_distance_km;
        $ratePerKm = (float) $store->rate_per_km;

        return round(max(0.0, ($distanceKm - $freeDistance) * $ratePerKm), 2);
    }

    public function isWithinRadius(Store $store, ?float $distanceKm): bool
    {
        if ($distanceKm === null) {
            return true;
        }

        $maxRadius = (float) $store->max_radius_km;

        if ($maxRadius <= 0) {
            return true;
        }

        return $distanceKm <= $maxRadius;
    }

    /**
     * Estimasi ongkir toko ke node tujuan.
     *
     * @return array{distance_km: float|null, cost: float, within_radius: bool}
     */
    public function estimate(Store $store, ?LocationNode $destination): array
    {
        $distance = null;

        if ($store->location_node_id && $destination?->id) {
            $distance = $this->dijkstra->distanceBetween($store->location_node_id, $destination->id);
        }

        return [
            'distance_km' => $distance,
            'cost' => $this->cost($store, $distance),
            'within_radius' => $this->isWithinRadius($store, $distance),
        ];
    }
}
