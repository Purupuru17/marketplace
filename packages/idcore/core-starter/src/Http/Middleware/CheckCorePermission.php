<?php

namespace IdCore\CoreStarter\Http\Middleware;

use Closure;
use IdCore\CoreStarter\Support\ActiveRole;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class CheckCorePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();
        $role = ActiveRole::get($user);

        $hasPermission = false;
        if ($role) {
            try {
                $hasPermission = $role->hasPermissionTo($permission);
            } catch (PermissionDoesNotExist $e) {
                // Permission belum dibuat -> dianggap tidak punya akses, bukan error server.
                $hasPermission = false;
            }
        }

        if (! $hasPermission) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Anda tidak memiliki akses : {$permission}",
                ], 403);
            }

            abort(403, "Anda tidak memiliki akses : {$permission}");
        }

        return $next($request);
    }
}
