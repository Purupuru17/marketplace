<?php

namespace IdCore\CoreStarter\Services;

use IdCore\CoreStarter\Models\Menu;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class MenuService
{
    /**
     * Susun collection flat jadi struktur tree bersarang (children di dalam parent).
     */
    public static function buildTree($menus, $parentId = null): array
    {
        $tree = [];

        foreach ($menus->where('parent_id', $parentId) as $menu) {
            $node = $menu->toArray();
            $node['children'] = self::buildTree($menus, $menu->id);
            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * Ubah tree jadi list flat dengan indentasi (untuk opsi <select> parent),
     * supaya keliatan levelnya tanpa perlu query lagi.
     */
    public static function flattenTreeForSelect(array $tree, int $depth = 0): array
    {
        $result = [];

        foreach ($tree as $node) {
            $result[] = [
                'id'    => $node['id'],
                'label' => str_repeat('— ', $depth) . $node['name'],
            ];
            $result = array_merge($result, self::flattenTreeForSelect($node['children'], $depth + 1));
        }

        return $result;
    }

    /**
     * Ambil semua ID descendant (anak, cucu, dst) dari satu menu.
     */
    public static function getDescendantIds(Menu $menu): array
    {
        $ids = [];

        foreach ($menu->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, self::getDescendantIds($child));
        }

        return $ids;
    }

    /**
     * Generate permission untuk action yang dicentang,
     * hapus permission untuk action yang di-uncheck
     * (otomatis cascade delete dari role_has_permissions & model_has_permissions).
     */
    public static function syncPermissionsForMenu(Menu $menu): void
    {
        $isGroup = empty($menu->url) || $menu->url === '#';

        if ($isGroup) {
            // Grup/kategori tidak punya URL sendiri, jadi tidak boleh generate permission apapun.
            return;
        }

        $module = Str::slug(basename($menu->url ?? $menu->name));
        $allActionKeys = array_keys(config('idcore.menu_actions'));
        $checkedActions = $menu->actions ?? [];

        foreach ($checkedActions as $action) {
            Permission::firstOrCreate([
                'name'       => "{$module}.{$action}",
                'guard_name' => 'web',
            ]);
        }

        $uncheckedActions = array_diff($allActionKeys, $checkedActions);

        foreach ($uncheckedActions as $action) {
            Permission::where('name', "{$module}.{$action}")
                ->where('guard_name', 'web')
                ->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

}