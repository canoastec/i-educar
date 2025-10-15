<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectStudentToExams
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user?->role === 'Aluno' && !$request->is('minhas-provas*')) {
                if ($request->is('/') || $request->is('web')) {
                    return redirect('/minhas-provas');
                }
            }
        }

        return $next($request);
    }
}
