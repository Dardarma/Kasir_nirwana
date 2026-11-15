<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Nirwana</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-responsive.css') }}">
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <strong>Nirwana</strong>
        </div>

        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Masuk untuk mengakses aplikasi</p>

                @if ($retryAfter > 0)
                    <div id="login-lockout" class="alert alert-danger py-2">
                        Terlalu banyak percobaan login. Coba lagi dalam
                        <span id="login-retry-seconds">{{ $retryAfter }}</span> detik.
                    </div>
                @elseif ($errors->any())
                    <div class="alert alert-danger py-2">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login.store') }}" method="POST">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="text" name="username" class="form-control" placeholder="Username"
                            value="{{ old('username') }}" autocomplete="username" required autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-user"></span></div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password"
                            autocomplete="current-password" required>
                        <div class="input-group-append">
                            <button type="button" id="toggle-password" class="btn btn-outline-secondary"
                                aria-controls="password" aria-label="Tampilkan password" title="Tampilkan password" hidden>
                                <span class="fas fa-eye" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-7">
                            <div class="icheck-primary">
                                <input type="checkbox" name="remember" id="remember" value="1">
                                <label for="remember">Ingat saya</label>
                            </div>
                        </div>
                        <div class="col-5">
                            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        const password = document.getElementById('password');
        const togglePassword = document.getElementById('toggle-password');
        const passwordIcon = togglePassword.querySelector('span');

        togglePassword.addEventListener('click', () => {
            const showPassword = password.type === 'password';
            const label = showPassword ? 'Sembunyikan password' : 'Tampilkan password';

            password.type = showPassword ? 'text' : 'password';
            togglePassword.setAttribute('aria-label', label);
            togglePassword.title = label;
            passwordIcon.classList.toggle('fa-eye', !showPassword);
            passwordIcon.classList.toggle('fa-eye-slash', showPassword);
        });

        togglePassword.hidden = false;

        const retrySeconds = document.getElementById('login-retry-seconds');

        if (retrySeconds) {
            const deadline = Date.now() + Number(retrySeconds.textContent) * 1000;
            const timer = window.setInterval(() => {
                const remaining = Math.ceil((deadline - Date.now()) / 1000);

                if (remaining <= 0) {
                    window.clearInterval(timer);
                    document.getElementById('login-lockout').remove();
                    return;
                }

                retrySeconds.textContent = remaining;
            }, 250);
        }
    </script>
</body>

</html>
