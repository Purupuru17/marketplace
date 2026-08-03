<?php

namespace IdCore\CoreStarter\Http\Controllers\Sistem;

use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Http\Requests\Sistem\MenuRequest;
use IdCore\CoreStarter\Models\Menu;
use IdCore\CoreStarter\Services\MenuService;
use Illuminate\Http\Request;

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

        $compact = [
            'tree'              => $tree,
            
            'title'             => 'Menu',
            'subtitle'          => 'Daftar Menu yang tersedia',
            
            'module'            => $this->module,
            'rolesName'         => $this->resourceName(),
            'breadcrumb'        => [[ 'Beranda', route('dashboard')], [ucwords($this->resourceName())]]
        ];
        return view('idcore::'.$this->module.'.index', $compact);
    }

    public function create()
    {
        $parents = Menu::orderBy('sort_by')->get();
        $parentTree = MenuService::flattenTreeForSelect(MenuService::buildTree($parents));

        $compact = [
            'menu'       => new Menu(),
            'parentTree' => $parentTree,

            'title'             => 'Menu',
            'subtitle'          => 'Atur Menu navigasi user',
            'action'            => route($this->module.'.store'),
            
            'module'            => $this->module,
            'breadcrumb'        => [['Beranda', route('dashboard')], 
                [ucwords($this->resourceName()), route($this->module.'.index')], ['Tambah Data']]

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
            'menu'              => $menu,
            'parentTree'        => $parentTree,

            'title'             => 'Menu',
            'subtitle'          => 'Atur Menu navigasi user',
            'action'            => route($this->module.'.update', $menu->id),
            
            'module'            => $this->module,
            'breadcrumb'        => [['Beranda', route('dashboard')], 
                [ucwords($this->resourceName()), route($this->module.'.index')], ['Tambah Data']]

        ];
        return view('idcore::'.$this->module.'.form', $compact);
    }

     public function update(MenuRequest $request, Menu $menu)
    {
        $validated = $request->validated();

        if (!empty($validated['parent_id'])) {
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
