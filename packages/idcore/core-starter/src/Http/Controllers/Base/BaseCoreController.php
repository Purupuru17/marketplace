<?php

namespace IdCore\CoreStarter\Http\Controllers\Base;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;

abstract class BaseCoreController implements HasMiddleware
{
    use AuthorizesRequests, ValidatesRequests;

    public static function middleware(): array
    {
        $resourceName = static::resourceName();
        $middlewares = [];

        foreach (config('idcore.permission_map') as $method => $suffix) {
            $permission = "{$resourceName}.{$suffix}";
            $middlewares[] = new Middleware("core_permission:{$permission}", only: [$method]);
        }

        return $middlewares;
    }

    protected static function resourceName(): string
    {
        $className = class_basename(static::class);
        $name = str_replace('Controller', '', $className);

        return Str::slug(Str::snake($name), '-');
    }
}
