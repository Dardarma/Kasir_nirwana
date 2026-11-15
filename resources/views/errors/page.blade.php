<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') | Nirwana</title>
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
</head>
<body class="bg-light">
    <main class="container py-5 text-center">
        <h1 class="display-4">@yield('code')</h1>
        <p class="lead">@yield('message')</p>
        <a href="{{ route('home') }}" class="btn btn-primary">Kembali ke Dashboard</a>
    </main>
</body>
</html>
