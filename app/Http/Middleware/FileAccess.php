<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FileAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Acesso negado');
        }

        $userTypeName = $user->type->name ?? '';

        $allowedTypes = $this->getAllowedTypes();

        if (!in_array($userTypeName, $allowedTypes)) {
            abort(403, 'Acesso negado');
        }

        return $next($request);
    }

    /**
     * Get allowed user types from settings table or fallback to default.
     *
     * @return array
     */
    private function getAllowedTypes(): array
    {
        $value = config('file_access.allowed_types') ?: '';
        
        $types = array_map('trim', explode(',', $value));
        return array_filter($types);
    }
}

