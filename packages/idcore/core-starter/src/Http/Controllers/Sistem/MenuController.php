<?php

namespace IdCore\CoreStarter\Http\Controllers\Sistem;

use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Http\Requests\Sistem\MenuRequest;
use IdCore\CoreStarter\Models\Menu;
use IdCore\CoreStarter\Services\MenuService;

class MenuController extends BaseCoreController
{
    private $module = 'sistem.menu';

    protected static function resourceName(): string
    {
        return 'menu';
    }

    public function index()
    {
        $menus = Menu::orderBy('sort_by')->get();
        $tree = MenuService::buildTree($menus);

        $rows = [];
        $flatten = function (array $nodes, int $depth = 0) use (&$rows, &$flatten) {
            foreach ($nodes as $node) {
                $isGroup = empty($node['url']) || $node['url'] === '#';

                $name = '<div class="flex items-center gap-3" style="padding-left: '.($depth * 24).'px;">'
                    .'<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg '
                    .($isGroup ? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' : 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300').'">'
                    .'<span class="text-base leading-none">'.($isGroup ? '&#128193;' : '&#9679;').'</span>'
                    .'</div>'
                    .'<div class="min-w-0">'
                    .'<p class="truncate font-semibold '.($isGroup ? 'text-gray-700 dark:text-gray-200' : 'text-gray-900 dark:text-white').'">'.e($node['name']).'</p>'
                    .'<p class="text-xs text-gray-500 dark:text-gray-400">'.($isGroup ? 'Group menu' : 'Menu link').'</p>'
                    .'</div>'
                    .'</div>';

                $url = $isGroup
                    ? '<span class="text-gray-400">-</span>'
                    : '<code class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">'.e($node['url']).'</code>';

                $status = $node['is_active']
                    ? '<span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold tracking-wide text-success-700 dark:bg-success-500/15 dark:text-success-400">Aktif</span>'
                    : '<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold tracking-wide text-gray-700 dark:bg-white/5 dark:text-gray-400">Nonaktif</span>';

                $actions = '';
                if (! empty($node['actions'])) {
                    $actions = collect($node['actions'])->map(fn ($a) => '<span class="inline-flex items-center rounded-full bg-warning-50 px-2.5 py-1 text-xs font-semibold tracking-wide text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">'.e($a).'</span>'
                    )->implode(' ');
                } else {
                    $actions = '<span class="text-sm text-gray-400">-</span>';
                }

                $rows[] = [
                    'id' => $node['id'],
                    'name' => $name,
                    'name_plain' => $node['name'],
                    'url' => $url,
                    'actions' => $actions,
                    'status' => $status,
                    'edit_url' => auth()->user()->can('menu.edit') ? route('sistem.menu.edit', $node['id']) : null,
                    'delete_url' => auth()->user()->can('menu.delete') ? route('sistem.menu.destroy', $node['id']) : null,
                ];

                if (count($node['children'])) {
                    $flatten($node['children'], $depth + 1);
                }
            }
        };
        $flatten($tree);

        $columns = [
            ['key' => 'name', 'label' => 'Nama Menu', 'sortable' => false, 'html' => true],
            ['key' => 'url', 'label' => 'URL', 'sortable' => false, 'html' => true],
            ['key' => 'actions', 'label' => 'Actions', 'sortable' => false, 'html' => true],
            ['key' => 'status', 'label' => 'Status', 'sortable' => false, 'html' => true],
        ];

        $compact = [
            'tree' => $tree,

            'title' => 'Menu',
            'subtitle' => 'Daftar Menu yang tersedia',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], [ucwords($this->resourceName())]],

            'columns' => $columns,
            'rows' => $rows,
        ];

        return view('idcore::'.$this->module.'.index', $compact);
    }

    public function create()
    {
        $parents = Menu::orderBy('sort_by')->get();
        $parentTree = MenuService::flattenTreeForSelect(MenuService::buildTree($parents));

        $compact = [
            'menu' => new Menu,
            'parentTree' => $parentTree,

            'title' => 'Menu',
            'subtitle' => 'Atur Menu navigasi user',
            'action' => route($this->module.'.store'),

            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')],
                [ucwords($this->resourceName()), route($this->module.'.index')], ['Tambah Data']],

        ];

        return view('idcore::'.$this->module.'.form', $compact);
    }

    public function store(MenuRequest $request)
    {
        $menu = Menu::create($request->validated());

        MenuService::syncPermissionsForMenu($menu);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        $excludedIds = MenuService::getDescendantIds($menu);
        $excludedIds[] = $menu->id;

        $parents = Menu::whereNotIn('id', $excludedIds)->orderBy('sort_by')->get();
        $parentTree = MenuService::flattenTreeForSelect(MenuService::buildTree($parents));

        $compact = [
            'menu' => $menu,
            'parentTree' => $parentTree,

            'title' => 'Menu',
            'subtitle' => 'Atur Menu navigasi user',
            'action' => route($this->module.'.update', $menu->id),

            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')],
                [ucwords($this->resourceName()), route($this->module.'.index')], ['Tambah Data']],

        ];

        return view('idcore::'.$this->module.'.form', $compact);
    }

    public function update(MenuRequest $request, Menu $menu)
    {
        $validated = $request->validated();

        if (! empty($validated['parent_id'])) {
            $forbidden = MenuService::getDescendantIds($menu);
            $forbidden[] = $menu->id;

            if (in_array((int) $validated['parent_id'], $forbidden)) {
                return back()
                    ->withInput()
                    ->withErrors(['parent_id' => 'Parent tidak boleh diri sendiri atau turunan menu ini.']);
            }
        }

        $menu->update($validated);
        MenuService::syncPermissionsForMenu($menu);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        if ($menu->children()->exists()) {
            return back()->with('error', 'Tidak bisa hapus menu yang masih punya sub-menu. Hapus/pindahkan sub-menu dulu.');
        }

        $menu->delete();

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Data berhasil dihapus.');
    }
}
