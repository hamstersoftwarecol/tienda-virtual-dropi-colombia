<x-guest-layout>
    <div class="mb-4 text-center">
        <h4 class="fw-bold mb-1">Recuperar Contraseña</h4>
        <p class="text-muted small">Indícanos tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-3 alert alert-info" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
            <label for="email" class="form-label fw-semibold small">Correo Electrónico</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                <input id="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="ejemplo@correo.com">
            </div>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary rounded-pill py-2">
                Enviar enlace de recuperación
            </button>
        </div>

        <div class="text-center small">
            <a href="{{ route('login') }}" class="text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Volver a Iniciar Sesión
            </a>
        </div>
    </form>
</x-guest-layout>
