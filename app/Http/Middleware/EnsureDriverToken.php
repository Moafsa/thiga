<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDriverToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$request->user()->tokenCan('driver')) {
            abort(403, __('Acesso restrito ao aplicativo do motorista.'));
        }

        $driver = $user->driver;

        if (!$driver || !$driver->is_active) {
            abort(403, __('Perfil de motorista não encontrado ou inativo.'));
        }

        $request->attributes->set('driver', $driver);

        return $next($request);
    }
}
















