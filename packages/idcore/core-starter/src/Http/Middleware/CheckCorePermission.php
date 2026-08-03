<?php

namespace IdCore\CoreStarter\Http\Middleware;

use Closure;
use IdCore\CoreStarter\Support\ActiveRole;
use Illuminate\Http\Request;

class CheckCorePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();
        $role = ActiveRole::get($user);

        if (!$role || !$role->hasPermissionTo($permission)) {
            abort(403, "Anda tidak memiliki akses : {$permission}");
        }

        return $next($request);
    }
}
