@extends('layouts.admin')

@section('title', 'Configuración de Dropi')
@section('page_header', 'Configuración de Dropi Colombia')

@section('content')
<div class="mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-gear-wide-connected text-primary me-2"></i>Configuración & Token de Dropi
            </h4>
            <p class="text-muted small mb-0">
                Pega y valida tu Token de autenticación de Dropi.co para vincular tu tienda con la plataforma de Dropshipping.
            </p>
        </div>

        <div>
            @if($dropiToken && $dropiToken->is_valid)
                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3 py-2 fs-6">
                    <i class="bi bi-patch-check-fill me-1"></i> Token Válido & Activo
                </span>
            @elseif($dropiToken && !empty($dropiToken->token))
                <span class="badge bg-warning-subtle text-warning border border-warning border-opacity-25 rounded-pill px-3 py-2 fs-6">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Requiere Validación
                </span>
            @else
                <span class="badge bg-secondary-subtle text-muted rounded-pill px-3 py-2 fs-6">
                    <i class="bi bi-slash-circle me-1"></i> Sin Token Configurado
                </span>
            @endif
        </div>
    </div>
</div>

@if(session('validation_result'))
    @php $val = session('validation_result'); @endphp
    <div class="alert {{ $val['is_valid'] ? 'alert-success' : 'alert-danger' }} border-0 shadow-sm rounded-4 p-4 mb-4">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="{{ $val['is_valid'] ? 'bg-success' : 'bg-danger' }} text-white rounded-circle p-2 fs-4">
                <i class="bi {{ $val['is_valid'] ? 'bi-check-lg' : 'bi-x-lg' }}"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 {{ $val['is_valid'] ? 'text-success' : 'text-danger' }}">
                    {{ $val['is_valid'] ? '¡Diagnóstico de Token Exitoso!' : 'Observación en la Validación del Token' }}
                </h5>
                <small class="text-muted">{{ $val['message'] }}</small>
            </div>
        </div>

        @if(!empty($val['payload']))
            <div class="bg-white p-3 rounded-3 border text-dark small">
                <div class="row g-2">
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted d-block">ID Usuario Dropi:</span>
                        <strong class="font-monospace text-primary fs-6">{{ $val['user_id'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted d-block">Tipo de Integración:</span>
                        <strong class="text-uppercase">{{ $val['integration_type'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted d-block">URL Vinculada:</span>
                        <code>{{ $val['integration_url'] ?? 'N/A' }}</code>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted d-block">Expiración:</span>
                        <span class="badge {{ empty($val['is_expired']) ? 'bg-success' : 'bg-danger' }}">
                            {{ $val['expires_at'] ? date('d/m/Y H:i', strtotime($val['expires_at'])) : 'Sin Expiración' }}
                        </span>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif

<div class="row g-4">
    <!-- Left Column: Form Settings -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                <i class="bi bi-key-fill text-primary me-2"></i>Credenciales de Autenticación Dropi
            </h5>

            <form action="{{ route('admin.dropi.settings.store') }}" method="POST">
                @csrf

                <!-- Store Name -->
                <div class="mb-3">
                    <label for="store" class="form-label small fw-bold">Nombre de la Tienda *</label>
                    <input type="text" name="store" id="store" class="form-control rounded-3" 
                           value="{{ old('store', $dropiToken->store ?? 'Tienda 1') }}" 
                           placeholder="Ej: Tienda 1, Tienda Principal" required>
                    <small class="text-muted">Identificador de la tienda para la sincronización con Dropi.</small>
                </div>

                <!-- Token Dropi -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="token" class="form-label small fw-bold mb-0">Token JWT de Autenticación Dropi *</label>
                        @if($dropiToken && !empty($dropiToken->token))
                            <span class="badge bg-light text-dark border small font-monospace">Longitud: {{ strlen($dropiToken->token) }} caracteres</span>
                        @endif
                    </div>
                    <textarea name="token" id="token" rows="5" class="form-control rounded-3 font-monospace small" 
                              placeholder="Pega aquí tu token JWT de Dropi (ej: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...)" required>{{ old('token', $dropiToken->token ?? '') }}</textarea>
                    <small class="text-muted">Genera este token desde tu panel de <strong>Dropi.co</strong> en la sección de integraciones.</small>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Sync Mode -->
                    <div class="col-md-6">
                        <label for="sync" class="form-label small fw-bold">Sincronización de Pedidos *</label>
                        <select name="sync" id="sync" class="form-select rounded-3" required>
                            <option value="AUTOMÁTICAMENTE" {{ old('sync', $dropiToken->sync ?? 'AUTOMÁTICAMENTE') === 'AUTOMÁTICAMENTE' ? 'selected' : '' }}>
                                ⚡ AUTOMÁTICAMENTE (Recomendado)
                            </option>
                            <option value="MANUALMENTE" {{ old('sync', $dropiToken->sync ?? '') === 'MANUALMENTE' ? 'selected' : '' }}>
                                ✍️ MANUALMENTE
                            </option>
                        </select>
                        <small class="text-muted">Define si los pedidos se envían a Dropi de forma automática o manual.</small>
                    </div>

                    <!-- API URL -->
                    <div class="col-md-6">
                        <label for="api_url" class="form-label small fw-bold">URL de la API Dropi</label>
                        <input type="url" name="api_url" id="api_url" class="form-control rounded-3" 
                               value="{{ old('api_url', $dropiToken->api_url ?? 'https://api.dropi.co/api/') }}" required>
                        <small class="text-muted">Servidor API de Dropi.</small>
                    </div>
                </div>

                <!-- Create Product Option -->
                <div class="form-check form-switch p-3 bg-light rounded-3 border mb-4">
                    <input class="form-check-input ms-0 me-3" type="checkbox" name="create_prod_empr" value="1" id="create_prod_empr" 
                           {{ old('create_prod_empr', $dropiToken->create_prod_empr ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark" for="create_prod_empr">
                        Crear productos automáticamente si no existen en la tienda
                    </label>
                    <small class="text-muted d-block ms-5">
                        Si está activo, al sincronizar ventas con Dropi se crearán los productos vinculados que no estén en la base de datos local.
                    </small>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-save me-1"></i> Guardar y Validar Token
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Token Validation & Inspector -->
    <div class="col-lg-5">
        <!-- Validation Action Card -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shield-check text-success me-2"></i>Estado del Token
                </h5>
                @if($dropiToken && $dropiToken->last_validated_at)
                    <span class="badge bg-light text-muted border small">
                        Validado: {{ $dropiToken->last_validated_at->diffForHumans() }}
                    </span>
                @endif
            </div>

            @if($dropiToken && !empty($dropiToken->token))
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 {{ $dropiToken->is_valid ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} border mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $dropiToken->is_valid ? 'bi-check-circle-fill text-success' : 'bi-exclamation-circle-fill text-warning' }} fs-4"></i>
                            <div>
                                <div class="fw-bold">{{ $dropiToken->is_valid ? 'Token Activo y Estructurado' : 'Token Sin Validar o Inválido' }}</div>
                                <small class="text-dark opacity-75">Tienda: {{ $dropiToken->store }}</small>
                            </div>
                        </div>
                    </div>

                    @if($tokenDetails)
                        <div class="bg-light p-3 rounded-3 border mb-3 small">
                            <h6 class="fw-bold text-dark small mb-2 border-bottom pb-1">
                                <i class="bi bi-info-circle text-primary me-1"></i>Información del Payload JWT:
                            </h6>
                            <ul class="list-unstyled mb-0 text-secondary">
                                <li class="mb-1"><strong>ID Usuario:</strong> <span class="font-monospace text-dark">{{ $tokenDetails['sub'] ?? 'N/A' }}</span></li>
                                <li class="mb-1"><strong>Audience (Aud):</strong> <span class="text-uppercase text-dark">{{ $tokenDetails['aud'] ?? 'N/A' }}</span></li>
                                <li class="mb-1"><strong>Tipo Token:</strong> <span class="text-dark">{{ $tokenDetails['token_type'] ?? 'N/A' }}</span></li>
                                <li class="mb-1"><strong>URL Vinculada:</strong> <code class="text-primary">{{ $tokenDetails['integration_url'] ?? 'N/A' }}</code></li>
                                @if(isset($tokenDetails['exp']))
                                    <li class="mb-1"><strong>Fecha de Expiración:</strong> <span class="text-dark">{{ date('Y-m-d H:i:s', $tokenDetails['exp']) }}</span></li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.dropi.settings.validate') }}" method="POST" class="d-grid mb-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-success rounded-pill fw-bold">
                            <i class="bi bi-arrow-repeat me-1"></i> 🔍 Validar Token Nuevamente
                        </button>
                    </form>

                    <form action="{{ route('admin.dropi.settings.destroy', $dropiToken->id) }}" method="POST" class="d-grid" onsubmit="return confirm('¿Seguro que deseas eliminar el token de Dropi?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">
                            <i class="bi bi-trash me-1"></i> Eliminar Token
                        </button>
                    </form>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-shield-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                    <p class="mb-0 small">No has ingresado ningún token todavía. Pega tu token de Dropi a la izquierda y presiona "Guardar y Validar".</p>
                </div>
            @endif
        </div>

        <!-- Help Guide Card -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <h6 class="fw-bold mb-2 text-dark">
                <i class="bi bi-question-circle text-primary me-2"></i>¿Cómo obtener tu Token en Dropi?
            </h6>
            <ol class="small text-muted ps-3 mb-0">
                <li class="mb-1">Ingresa a tu cuenta oficial en <a href="https://app.dropi.co" target="_blank" class="fw-semibold text-primary">app.dropi.co</a>.</li>
                <li class="mb-1">Ve al menú <strong>Integraciones</strong> &gt; <strong>WooCommerce / Tienda Virtual</strong>.</li>
                <li class="mb-1">Genera o copia el <strong>Token JWT de Autenticación</strong>.</li>
                <li class="mb-0">Pégalo en el campo de texto y haz clic en <strong>Guardar y Validar</strong>.</li>
            </ol>
        </div>
    </div>
</div>
@endsection
