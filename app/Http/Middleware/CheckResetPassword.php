<?php

namespace App\Http\Middleware;

use App\Services\ForceUserChangePasswordService;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckResetPassword
{
    public const AUTH_VIA_GOOGLE_SESSION_KEY = 'auth_via_google';

    public function __construct(ForceUserChangePasswordService $forceUserChangePasswordService)
    {
        $this->forceUserChangePasswordService = $forceUserChangePasswordService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (empty($user) || empty($user->employee)) {
            return $next($request);
        }

        if ($this->shouldSkipForcedPasswordChange($request, $user)) {
            return $next($request);
        }

        $this->validateUserExpirationPassword($user);

        if ($user->employee->force_reset_password) {
            return redirect()->route('change-password');
        }

        return $next($request);
    }

    private function shouldSkipForcedPasswordChange(Request $request, Authenticatable $user): bool
    {
        return $user->role === 'Aluno'
            && $request->session()->get(self::AUTH_VIA_GOOGLE_SESSION_KEY) === true;
    }

    public function validateUserExpirationPassword(Authenticatable $user)
    {
        $this->forceUserChangePasswordService->execute($user);
    }
}
