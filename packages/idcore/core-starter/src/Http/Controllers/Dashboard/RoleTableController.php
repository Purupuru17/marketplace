<?php

namespace IdCore\CoreStarter\Http\Controllers\Dashboard;

use IdCore\CoreStarter\Services\DataTableService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\Permission\Models\Role;

class RoleTableController extends Controller
{
    /**
     * Endpoint server-driven untuk tabel Role di dashboard.
     * Tidak extends BaseCoreController (tanpa core_permission) karena
     * halaman dashboard memang hanya butuh autentikasi.
     */
    public function index(Request $request)
    {
        return DataTableService::process(
            $request,
            Role::query()->withCount('users'),
            ['name', 'guard_name']
        );
    }
}
