<?php

namespace IdCore\CoreStarter\Support;

use Spatie\Permission\Models\Role;

class ActiveRole
{
    /**
     * Ambil role yang sedang aktif untuk user ini.
     * Auto-fallback + self-healing kalau session kosong atau
     * role yang tersimpan di session ternyata sudah dicabut admin.
     */
    public static function get($user): ?Role
    {
        if (!$user) return null;

        $roleName = session('active_role');

        if ($roleName && $user->hasRole($roleName)) {
            return Role::findByName($roleName, $user->guard_name ?? 'web');
        }

        // Session kosong / role sudah dicabut -> jatuhkan ke role pertama yang MASIH dia punya
        $fallback = $user->roles->first();

        if ($fallback) {
            session(['active_role' => $fallback->name]);

            return $fallback;
        }

        return null;
    }

    public static function set($user, string $roleName): bool
    {
        if (!$user->hasRole($roleName)) {
            return false; // cegah user pindah ke role yang bukan miliknya
        }

        session(['active_role' => $roleName]);

        return true;
    }
}
