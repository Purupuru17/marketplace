<?php

namespace App\Services\Pricing;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use Illuminate\Support\Collection;

class PromotionPricingService
{
    /**
     * @return array{original: float, effective: float, discount: float, promotion: Promotion|null}
     */
    public function pricing(ProductVariant $variant): array
    {
        $original = (float) $variant->price;
        $best = null;
        $bestEffective = $original;

        foreach ($this->applicablePromotions($variant->product) as $promotion) {
            $effective = $promotion->type === 'percentage'
                ? $original * (1 - ((float) $promotion->value / 100))
                : $original - (float) $promotion->value;

            if ($effective < $bestEffective) {
                $bestEffective = max(0, $effective);
                $best = $promotion;
            }
        }

        $effective = round($bestEffective, 2);

        return [
            'original' => $original,
            'effective' => $effective,
            'discount' => round($original - $effective, 2),
            'promotion' => $best,
        ];
    }

    /**
     * @return Collection<int, Promotion>
     */
    protected function applicablePromotions(Product $product): Collection
    {
        $now = now();

        $promotions = $product->relationLoaded('promotions')
            ? $product->promotions
            : $product->promotions()->get();

        return $promotions
            ->filter(fn (Promotion $promotion) => $promotion->status === 'active'
                && $promotion->starts_at?->lte($now)
                && $promotion->ends_at?->gte($now)
                && ($promotion->source === 'platform' || $promotion->store_id === $product->store_id))
            ->values();
    }
}
