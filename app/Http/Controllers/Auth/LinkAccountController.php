<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LegacyEmployee;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LinkAccountController extends Controller
{
    public function showLinkFormat(Request $request)
    {
        $email = session('google_email');

        if (!$email) {
            return redirect('/login')->withErrors(['login' => 'Sessão expirada. Tente logar com Google novamente.']);
        }

        return view('auth.google-link', compact('email'));
    }

    public function link(Request $request)
    {
        $request->validate([
            'matricula' => 'required',
            'password' => 'required',
        ]);

        $email = session('google_email');

        if (!$email) {
            return redirect('/login')->withErrors(['login' => 'Sessão expirada. Tente logar com Google novamente.']);
        }

        $employee = LegacyEmployee::where('matricula', $request->matricula)->first();

        if (!$employee || !Hash::check($request->password, $employee->senha)) {
            return back()->withErrors(['login' => 'Matrícula ou senha incorretos.']);
        }

        $user = User::find($employee->ref_cod_pessoa_fj);

        if (!$user) {
            return back()->withErrors(['login' => 'Usuário não encontrado no sistema.']);
        }

        $employee->email = $email;
        $employee->save();

        $person = $user->person;
        if ($person) {
            $person->email = $email;
            $person->save();
        }

        Auth::login($user);

        session()->forget('google_email');

        return redirect()->intended();
    }
}
