<?php

namespace IdCore\CoreStarter\Http\Middleware;

use Closure;
use IdCore\CoreStarter\Services\ActivityLogService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldAudit($request, $response)) {
            ActivityLogService::record(
                $request->user('web')?->id,
                $this->eventFor($request),
                ucfirst($this->eventFor($request)).' — '.$request->method().' '.$request->path(),
                null,
                ['path' => $request->path()],
            );
        }

        return $response;
    }

    private function shouldAudit(Request $request, Response $response): bool
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        if (! $request->user('web') || $request->expectsJson() || $request->is('api/*')) {
            return false;
        }

        // Login/logout sudah dicatat manual di LoginController.
        return ! in_array($request->path(), ['login', 'logout']);
    }

    private function eventFor(Request $request): string
    {
        return match ($request->method()) {
            'POST' => 'created',
            'DELETE' => 'deleted',
            default => 'updated',
        };
    }
}
