<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') · {{ config('app.name', 'SIMAC') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #1e293b 0%, #2563eb 100%);
        }
        .auth-card { border: none; border-radius: 1rem; box-shadow: 0 10px 40px rgba(0,0,0,.25); }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="text-center text-white mb-4">
                <i class="bi bi-snow2 fs-1"></i>
                <h1 class="h3 mt-2 mb-0">SIMAC</h1>
                <p class="opacity-75 small">Sistem Manajemen Maintenance AC</p>
            </div>
            <div class="card auth-card">
                <div class="card-body p-4">
                    @include('partials.flash')
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
