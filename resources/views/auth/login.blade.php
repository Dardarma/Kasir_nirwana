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

                @if ($errors->any())
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
                        <input type="password" name="password" class="form-control" placeholder="Password"
                            autocomplete="current-password" required>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
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
</body>

</html>
