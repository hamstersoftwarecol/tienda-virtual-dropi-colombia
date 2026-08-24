<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'NovaStore') }} - Autenticación</title>

    <!-- Vite Assets -->
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light d-flex align-items-center min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <!-- Brand Header -->
                <div class="text-center mb-4">
                    <a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                        <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                            <i class="bi bi-shop-window fs-4"></i>
                        </div>
                        <span class="fs-3 fw-bold brand-gradient">NovaStore</span>
                    </a>
                </div>

                <!-- Card Box -->
                <div class="card shadow border-0 rounded-4 p-4 p-md-4">
                    <div class="card-body p-0">
                        {{ $slot }}
                    </div>
                </div>

                <!-- Footer info -->
                <div class="text-center mt-4 text-muted small">
                    <a href="{{ route('home') }}" class="text-decoration-none text-muted">
                        <i class="bi bi-arrow-left me-1"></i> Volver a la tienda
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
