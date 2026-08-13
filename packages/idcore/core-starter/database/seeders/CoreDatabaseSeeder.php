<?php

namespace IdCore\CoreStarter\Database\Seeders;

use App\Models\User;
use IdCore\CoreStarter\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CoreDatabaseSeeder extends Seeder
{
    protected array $permissions = [
        'user' => ['index', 'create', 'edit', 'delete', 'detail', 'ajax'],
        'group' => ['index', 'create', 'edit', 'delete'],
        'menu' => ['index', 'create', 'edit', 'delete'],
        'hak-akses' => ['index', 'edit'],
    ];

    public function run(): void
    {
        // Reset cache Spatie agar permission baru langsung aktif
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seedPermissions();
        $this->seedSuperAdmin();
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
    }

    protected function seedSuperAdmin(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        // Sinkronisasi ulang semua permission terbaru ke Super Admin
        $superAdminRole->syncPermissions(Permission::all());

        $superAdmin = User::firstOrCreate(
            ['email' => 'super@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole($superAdminRole);
        $superAdmin->update(['default_role_id' => $superAdminRole->id]);
    }

    protected function seedMenus(): void
    {
        $menuSistem = Menu::firstOrCreate(
            ['name' => 'Sistem', 'parent_id' => null],
            ['url' => '#', 'icon' => 'heroicon-o-cog-6-tooth', 'sort_by' => 99]
        );
        $childMenus = [
            ['name' => 'User', 'url' => '/sistem/user', 'sort_by' => 1, 'icon' => 'heroicon-o-users', 'actions' => $this->permissions['user']],
            ['name' => 'Group', 'url' => '/sistem/group', 'sort_by' => 2, 'icon' => 'heroicon-o-shield-check', 'actions' => $this->permissions['group']],
            ['name' => 'Menu', 'url' => '/sistem/menu', 'sort_by' => 3, 'icon' => 'heroicon-o-list-bullet', 'actions' => $this->permissions['menu']],
            ['name' => 'Hak Akses', 'url' => '/sistem/hak-akses', 'sort_by' => 4, 'icon' => 'heroicon-o-lock-closed', 'actions' => $this->permissions['hak-akses']],
        ];

        foreach ($childMenus as $menu) {
            Menu::firstOrCreate(
                ['name' => $menu['name'], 'parent_id' => $menuSistem->id],
                $menu
            );
        }
    }
}
