<?php

namespace IdCore\CoreStarter\Http\Controllers\Sistem;

use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class MenuController extends BaseCoreController
{
    protected static function resourceName(): string
    {
        return 'menu';
    }

    public function index()
    {
        $menus = Menu::orderBy('sort_by')->get();
        $tree = $this->buildTree($menus);

        return view('idcore::sistem.menu.index', compact('tree'));
    }

    public function create()
    {
        $parents = Menu::orderBy('sort_by')->get();
        $parentTree = $this->flattenTreeForSelect($this->buildTree($parents));

        return view('idcore::sistem.menu.form', [
            'menu' => new Menu,
            'parentTree' => $parentTree,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateMenu($request);
        $menu = Menu::create($validated);

        $this->syncPermissionsForMenu($menu);

        return redirect()
            ->route('sistem.menu.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        $excludedIds = $this->getDescendantIds($menu);
        $excludedIds[] = $menu->id;

        $parents = Menu::whereNotIn('id', $excludedIds)->orderBy('sort_by')->get();
        $parentTree = $this->flattenTreeForSelect($this->buildTree($parents));

        return view('idcore::sistem.menu.form', compact('menu', 'parentTree'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $this->validateMenu($request, $menu->id);

        if (! empty($validated['parent_id'])) {
            $forbidden = $this->getDescendantIds($menu);
            $forbidden[] = $menu->id;

            if (in_array((int) $validated['parent_id'], $forbidden)) {
                return back()
                    ->withInput()
                    ->withErrors(['parent_id' => 'Parent tidak boleh diri sendiri atau turunan menu ini.']);
            }
        }

        $menu->update($validated);
        $this->syncPermissionsForMenu($menu);

        return redirect()
            ->route('sistem.menu.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        if ($menu->children()->exists()) {
            return back()->with('error', 'Tidak bisa hapus menu yang masih punya sub-menu. Hapus/pindahkan sub-menu dulu.');
        }

        $menu->delete();

        return redirect()
            ->route('sistem.menu.index')
            ->with('success', 'Menu berhasil dihapus.');
    }

    // ==================== HELPER PRIVATE ====================

    private function validateMenu(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'url' => 'nullable|string|max:150',
            'icon' => 'nullable|string|max:100',
            'actions' => 'nullable|array',
            'actions.*' => 'in:'.implode(',', array_keys(config('idcore.menu_actions'))),
            'parent_id' => 'nullable|exists:menus,id',
            'sort_by' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
    }

    /**
     * Susun collection flat jadi struktur tree bersarang (children di dalam parent).
     */
    private function buildTree($menus, $parentId = null): array
    {
        $tree = [];

        foreach ($menus->where('parent_id', $parentId) as $menu) {
            $node = $menu->toArray();
            $node['children'] = $this->buildTree($menus, $menu->id);
            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * Ubah tree jadi list flat dengan indentasi (untuk opsi <select> parent),
     * supaya keliatan levelnya tanpa perlu query lagi.
     */
    private function flattenTreeForSelect(array $tree, int $depth = 0): array
    {
        $result = [];

        foreach ($tree as $node) {
            $result[] = [
                'id' => $node['id'],
                'label' => str_repeat('— ', $depth).$node['name'],
            ];
            $result = array_merge($result, $this->flattenTreeForSelect($node['children'], $depth + 1));
        }

        return $result;
    }

    /**
     * Ambil semua ID descendant (anak, cucu, dst) dari satu menu.
     */
    private function getDescendantIds(Menu $menu): array
    {
        $ids = [];

        foreach ($menu->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }

        return $ids;
    }

    /**
     * Generate permission untuk action yang dicentang,
     * hapus permission untuk action yang di-uncheck
     * (otomatis cascade delete dari role_has_permissions & model_has_permissions).
     */
    private function syncPermissionsForMenu(Menu $menu): void
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
                'name' => "{$module}.{$action}",
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
