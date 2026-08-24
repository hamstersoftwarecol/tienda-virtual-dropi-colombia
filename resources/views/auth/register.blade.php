<x-guest-layout>
    <div class="mb-4 text-center">
        <h4 class="fw-bold mb-1">Crea tu cuenta</h4>
        <p class="text-muted small">Únete a NovaStore para disfrutar de descuentos y seguimiento de pedidos.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold small">Nombre Completo</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
                <input id="name" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Ej: Juan Pérez">
            </div>
            @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold small">Correo Electrónico</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                <input id="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="ejemplo@correo.com">
            </div>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold small">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock"></i></span>
                <input id="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres">
            </div>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold small">Confirmar Contraseña</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock-fill"></i></span>
                <input id="password_confirmation" class="form-control border-start-0 ps-0 @error('password_confirmation') is-invalid @enderror" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repite tu contraseña">
            </div>
            @error('password_confirmation')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary rounded-pill py-2">
                Crear Cuenta
            </button>
        </div>

        <div class="text-center small text-muted">
            ¿Ya tienes una cuenta? 
            <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">
                Inicia sesión
            </a>
        </div>
    </form>
</x-guest-layout>
