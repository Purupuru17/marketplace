<?php

namespace Database\Seeders;

use App\Models\LocationDistance;
use App\Models\LocationNode;
use IdCore\CoreStarter\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LocationDataSeeder extends Seeder
{
    protected array $permissions = [
        'location-node' => ['index', 'create', 'edit', 'delete'],
        'location-distance' => ['index', 'create', 'edit', 'delete'],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seedPermissions();
        $this->seedSampleData();
        $this->seedMenus();
    }

    protected function seedPermissions(): void
    {
        foreach ($this->permissions as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        $administrator = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $administrator->syncPermissions(Permission::all());
    }

    protected function seedSampleData(): void
    {
        $nodes = collect(['Kota Jakarta', 'Kota Bandung', 'Kota Cirebon', 'Kota Semarang', 'Kota Surabaya', 'Kota Terpencil'])
            ->map(fn (string $name) => LocationNode::updateOrCreate(['name' => $name]));

        [$jakarta, $bandung, $cirebon, $semarang, $surabaya, $terpencil] = $nodes->all();

        $edges = [
            [$jakarta, $bandung, 150],
            [$bandung, $cirebon, 130],
            [$cirebon, $semarang, 210],
            [$semarang, $surabaya, 320],
            [$jakarta, $surabaya, 900],
        ];

        foreach ($edges as [$origin, $destination, $distance]) {
            LocationDistance::firstOrCreate(
                ['origin_node_id' => $origin->id, 'destination_node_id' => $destination->id],
                ['distance_km' => $distance]
            );
        }
    }

    protected function seedMenus(): void
    {
        $menuMaster = Menu::where('name', 'Master Data')->whereNull('parent_id')->first();

        if (! $menuMaster) {
            return;
        }

        $childMenus = [
            ['name' => 'Node Lokasi', 'url' => '/master/location-node', 'sort_by' => 5, 'icon' => 'heroicon-o-map-pin', 'actions' => $this->permissions['location-node']],
            ['name' => 'Jarak Antar Node', 'url' => '/master/location-distance', 'sort_by' => 6, 'icon' => 'heroicon-o-arrows-right-left', 'actions' => $this->permissions['location-distance']],
        ];

        foreach ($childMenus as $menu) {
            Menu::firstOrCreate(
                ['name' => $menu['name'], 'parent_id' => $menuMaster->id],
                $menu
            );
        }
    }
}
