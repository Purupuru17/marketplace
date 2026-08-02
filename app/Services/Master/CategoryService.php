<?php

namespace App\Services\Master;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CategoryService
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Category::query()
            ->with('parent')
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->where('name', 'like', '%'.$filters['search'].'%');
            })
            ->when(! empty($filters['parent_id']), function ($query) use ($filters) {
                $query->where('parent_id', $filters['parent_id']);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function options(?Category $except = null): array
    {
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();

        if ($except) {
            $excluded = $this->excludedParentIds($except);
            $categories = $categories->reject(fn (Category $category) => in_array($category->id, $excluded));
        }

        $options = [];
        $this->flattenTree($this->buildTree($categories), $options);

        return $options;
    }

    public function excludedParentIds(Category $category): array
    {
        return array_merge($this->descendantIds($category), [$category->id]);
    }

    public function create(array $data): Category
    {
        $data['slug'] = $this->uniqueSlug($data['name']);

        return Category::create($data);
    }

    public function update(Category $category, array $data): bool
    {
        if (($data['name'] ?? null) !== $category->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        }

        return $category->update($data);
    }

    public function delete(Category $category): ?bool
    {
        return $category->delete();
    }

    protected function descendantIds(Category $category): array
    {
        $ids = [];

        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->descendantIds($child));
        }

        return $ids;
    }

    protected function buildTree(iterable $categories, ?string $parentId = null): array
    {
        $nodes = [];

        foreach ($categories as $category) {
            if ($category->parent_id === $parentId) {
                $nodes[] = [
                    'category' => $category,
                    'children' => $this->buildTree($categories, $category->id),
                ];
            }
        }

        return $nodes;
    }

    protected function flattenTree(array $nodes, array &$options, int $depth = 0): void
    {
        foreach ($nodes as $node) {
            $options[$node['category']->id] = str_repeat('-- ', $depth).$node['category']->name;
            $this->flattenTree($node['children'], $options, $depth + 1);
        }
    }

    protected function uniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $base = $slug;
        $counter = 2;

        while (Category::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
