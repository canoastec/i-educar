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
            return redirect()->route('aluno.login')->withErrors(['login' => 'E-mail não informado pelo Google.']);
        }

        $user = User::query()
            ->whereHas('employee', fn($q) => $q->where('email', $email))
            ->first();

        if (empty($user)) {
            return redirect()->route('aluno.login')->withErrors(['login' => 'Usuário não encontrado.']);
        }

        if ($user->isInactive()) {
            return redirect()->route('aluno.login')->withErrors(['login' => $user->employee->motivo ?: __('auth.inactive')]);
        }

        if ($user->role !== 'Aluno') {
            return redirect()->route('aluno.login')->withErrors(['login' => 'Acesso permitido apenas para alunos.']);
        }

        $hasActiveEnrollment = $user->person
            ?->student
            ?->registrations()
            ->where('ativo', 1)
            ->whereHas('activeEnrollments')
            ->exists();

        if (!$hasActiveEnrollment) {
            return redirect()->route('aluno.login')->withErrors(['login' => 'Acesso permitido apenas para alunos matriculados.']);
        }

        Auth::login($user);

        return redirect()->intended();
    }
}


