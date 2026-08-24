<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentLoginController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('guest');

        if (empty($request->query('force'))) {
            $this->middleware('ieducar.suspended')->except('showLoginForm');
        }
    }

    public function showLoginForm()
    {
        return view('auth.aluno.login');
    }
}
