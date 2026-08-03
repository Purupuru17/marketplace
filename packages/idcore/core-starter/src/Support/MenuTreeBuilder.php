<?php

namespace IdCore\CoreStarter\Support;

use IdCore\CoreStarter\Models\Menu;
use Illuminate\Support\Str;

class MenuTreeBuilder
{
    public static function forUser($user): array
    {
        $menus = Menu::where('is_active', true)->orderBy('sort_by')->get();
        $tree  = static::build($menus, null);
        $tree  = static::filterByPermission($tree, $user);

        return static::markActive($tree);   // ← baris ini yang menambahkan is_current & has_active_child
    }

    protected static function build($menus, $parentId): array
    {
        $result = [];

        foreach ($menus->where('parent_id', $parentId) as $menu) {
            $node = $menu->toArray();
            $node['module']   = Str::slug(basename($menu->url ?? $menu->name));
            $node['children'] = static::build($menus, $menu->id);
            $result[] = $node;
        }

        return $result;
    }

    /**
     * Grup (url # atau kosong) ditampilkan kalau punya minimal 1 anak yang visible.
     * Menu dengan link nyata ditampilkan kalau user punya permission "{module}.index".
     */
    protected static function filterByPermission(array $nodes, $user): array
    {
        $role = ActiveRole::get($user);
        $visible = [];

        foreach ($nodes as $node) {
            $node['children'] = static::filterByPermission($node['children'], $user);
            $isGroup = empty($node['url']) || $node['url'] === '#';

            if ($isGroup) {
                if (count($node['children']) > 0) $visible[] = $node;
                continue;
            }

            if ($role && $role->hasPermissionTo("{$node['module']}.index")) {
                $visible[] = $node;
            }
        }

        return $visible;
    }

    protected static function markActive(array $nodes): array
    {
        foreach ($nodes as &$node) {
            $node['children'] = static::markActive($node['children']);

            $node['is_current'] = !empty($node['url'])
                && $node['url'] !== '#'
                && request()->is(ltrim($node['url'], '/') . '*');

            $node['has_active_child'] = collect($node['children'])
                ->contains(fn($c) => $c['is_current'] || $c['has_active_child']);
        }

        return $nodes;
    }
}
