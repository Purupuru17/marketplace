<?php

namespace App\Services\Master;

use App\Models\Attribute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttributeService
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Attribute::query()
            ->withCount('values')
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->where('name', 'like', '%'.$filters['search'].'%');
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Attribute
    {
        $attribute = Attribute::create(['name' => $data['name']]);

        $this->syncValues($attribute, $data['values'] ?? []);

        return $attribute;
    }

    public function update(Attribute $attribute, array $data): bool
    {
        $updated = $attribute->update(['name' => $data['name']]);

        $this->syncValues($attribute, $data['values'] ?? []);

        return $updated;
    }

    public function delete(Attribute $attribute): ?bool
    {
        return $attribute->delete();
    }

    public function syncValues(Attribute $attribute, array $values): void
    {
        $values = array_values(array_filter(array_map(
            fn ($value) => trim((string) $value),
            $values
        ), fn ($value) => $value !== ''));

        $existing = $attribute->values()->pluck('value', 'id');

        foreach ($existing as $id => $value) {
            if (! in_array($value, $values)) {
                $attribute->values()->whereKey($id)->delete();
            }
        }

        foreach ($values as $value) {
            if (! $existing->contains($value)) {
                $attribute->values()->create(['value' => $value]);
            }
        }
    }
}
