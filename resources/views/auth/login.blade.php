<x-guest-layout>
    <div class="mb-4 text-center">
        <h4 class="fw-bold mb-1">Bienvenido de nuevo</h4>
        <p class="text-muted small">Ingresa tus credenciales para acceder a tu cuenta.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-3 alert alert-info" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold small">Correo Electrónico</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                <input id="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="ejemplo@correo.com">
            </div>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="form-label fw-semibold small mb-0">Contraseña</label>
                @if (Route::has('password.request'))
                    <a class="small text-primary text-decoration-none" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock"></i></span>
                <input id="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            </div>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="form-check mb-4">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label small text-muted">
                Recordar mi sesión
            </label>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary rounded-pill py-2">
                Iniciar Sesión
            </button>
        </div>

        <div class="text-center small text-muted">
            ¿No tienes una cuenta? 
            <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">
                Regístrate aquí
            </a>
        </div>
    </form>
</x-guest-layout>
