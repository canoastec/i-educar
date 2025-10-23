<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleCallbackController
{
    public function __invoke()
    {
        $googleUser = Socialite::driver('google')->user();
        $email = $googleUser->getEmail();

        if (empty($email)) {
            return redirect('/login')->withErrors(['login' => 'E-mail não informado pelo Google.']);
        }

        $user = User::query()
            ->whereHas('employee', fn($q) => $q->where('email', $email))
            ->first();

        if (empty($user)) {
            return redirect('/login')->withErrors(['login' => 'Usuário não encontrado.']);
        }

        if ($user->isInactive()) {
            return redirect('/login')->withErrors(['login' => $user->employee->motivo ?: __('auth.inactive')]);
        }

        if ($user?->role !== 'Aluno') {
            return redirect('/login')->withErrors(['login' => 'Acesso permitido apenas para alunos.']);
        }

        Auth::login($user);

        return redirect()->intended();
    }
}


