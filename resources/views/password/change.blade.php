@extends('layout.public')

@section('content')
    <h2>Alterar senha</h2>
    <p>Para sua segurança, sua senha deverá ser alterada.</p>
    <form action="{{ route('post-change-password') }}" method="post">

        {{csrf_field()}}

        <label for="login">Login:</label>
        <input type="text" name="login" id="login" value="{{ old('login', $login ?? '') }}" readonly>

        <label for="password">Senha:</label>
        <div class="password-field">
            <input type="password" name="password" id="password">
            <i class="fa fa-eye-slash password-toggle" id="eye-password" onclick="showPassword('password', 'eye-password')" onkeyup="showPassword('password', 'eye-password')" aria-hidden="true"></i>
        </div>

        <label for="password-confirm">Confirme:</label>
        <div class="password-field">
            <input type="password" name="password_confirmation" id="password-confirm">
            <i class="fa fa-eye-slash password-toggle" id="eye-password-confirm" onclick="showPassword('password-confirm', 'eye-password-confirm')" onkeyup="showPassword('password-confirm', 'eye-password-confirm')" aria-hidden="true"></i>
        </div>

        <button type="submit" class="submit">Entrar</button>
    </form>

    <style>
        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 40px;
        }

        .password-field .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #188ad1;
        }
    </style>

    <script>
        function showPassword(inputId, eyeId) {
            var input = document.getElementById(inputId);
            var eye = document.getElementById(eyeId);

            if (input.type === "password") {
                input.type = "text";
                eye.classList.remove("fa-eye-slash");
                eye.classList.add("fa-eye");
            } else {
                input.type = "password";
                eye.classList.remove("fa-eye");
                eye.classList.add("fa-eye-slash");
            }
        }
    </script>
@endsection
