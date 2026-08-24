@extends('layouts.admin')

@section('title', 'Integraciones WooCommerce & Dropi')
@section('page_header', 'Centro de Integraciones: WooCommerce & Dropi API')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-plug-fill text-primary me-2"></i>Conexión con Plataformas & Dropi.co</h4>
    <p class="text-muted small mb-0">
        Nuestra tienda simula de forma nativa la <strong>REST API de WooCommerce (WordPress v3)</strong> para que puedas conectar tu cuenta de <strong>Dropi.co</strong> (o plugins de dropshipping) usando tus claves Consumer Key y Consumer Secret.
    </p>
</div>

@if(session('key_generated'))
    @php $newKey = session('key_generated'); @endphp
    <div class="alert alert-success border-0 shadow-sm rounded-4 p-4 mb-4">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="bg-success text-white rounded-circle p-2 fs-4">
                <i class="bi bi-check-lg"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-success">¡Nueva Clave WooCommerce Generada con Éxito!</h5>
                <small class="text-muted">Copia estas credenciales ahora. Por seguridad, el Consumer Secret no se volverá a mostrar completo.</small>
            </div>
        </div>

        <div class="bg-white p-3 rounded-3 border mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="small text-muted fw-bold">Consumer Key (CK):</label>
                    <div class="input-group input-group-sm mt-1">
                        <input type="text" class="form-control font-monospace bg-light" value="{{ $newKey['consumer_key'] }}" id="newCK" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('newCK').value); window.showToast('Consumer Key copiada al portapapeles');"><i class="bi bi-clipboard"></i></button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="small text-muted fw-bold">Consumer Secret (CS):</label>
                    <div class="input-group input-group-sm mt-1">
                        <input type="text" class="form-control font-monospace bg-light" value="{{ $newKey['consumer_secret'] }}" id="newCS" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('newCS').value); window.showToast('Consumer Secret copiada al portapapeles');"><i class="bi bi-clipboard"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row g-4">
    <!-- Left Column: WooCommerce API Keys & Connection Instructions -->
    <div class="col-lg-7">
        <!-- Connection Details Card -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-wordpress text-primary me-2"></i>Conexión WooCommerce para Dropi
                </h5>
                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                    <i class="bi bi-check-circle-fill"></i> API v3 Activa
                </span>
            </div>

            <p class="small text-secondary mb-3">
                Para conectar tu tienda a <strong>Dropi.co</strong>, ingresa a tu panel de Dropi > <strong>Integraciones > WooCommerce</strong> y usa los siguientes datos:
            </p>

            <div class="bg-light p-3 rounded-3 border mb-3 small">
                <div class="mb-2">
                    <span class="text-muted d-block fw-bold">URL de la Tienda (Website URL):</span>
                    <div class="input-group input-group-sm mt-1">
                        <input type="text" class="form-control font-monospace bg-white" value="{{ url('/') }}" id="storeUrl" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('storeUrl').value); window.showToast('URL copiada al portapapeles');"><i class="bi bi-clipboard"></i> Copiar</button>
                    </div>
                </div>

                <div>
                    <span class="text-muted d-block fw-bold">Endpoint REST API WooCommerce:</span>
                    <code class="text-primary">{{ url('/wp-json/wc/v3') }}</code>
                </div>
            </div>

            <!-- Steps -->
            <div class="small text-muted">
                <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-list-check text-primary me-1"></i>Pasos de Vinculación en Dropi:</h6>
                <ol class="ps-3 mb-0">
                    <li>Inicia sesión en tu cuenta de <a href="https://dropi.co" target="_blank" class="fw-bold">Dropi.co</a>.</li>
                    <li>Ve a <strong>Integraciones</strong> &rarr; <strong>WooCommerce</strong>.</li>
                    <li>Pega la <strong>URL de la tienda</strong>, el <strong>Consumer Key</strong> y el <strong>Consumer Secret</strong> generados abajo.</li>
                    <li>¡Listo! Dropi sincronizará los pedidos y productos en tiempo real.</li>
                </ol>
            </div>
        </div>

        <!-- Active API Keys Table -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">Claves API de WooCommerce ({{ $apiKeys->count() }})</h5>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#generateKeyModal">
                    <i class="bi bi-plus-lg me-1"></i> Generar Nueva Clave
                </button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light small text-muted text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Descripción</th>
                            <th class="py-3">Clave Truncada</th>
                            <th class="py-3">Permisos</th>
                            <th class="py-3">Último Acceso</th>
                            <th class="pe-4 py-3 text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($apiKeys as $key)
                            <tr class="border-bottom">
                                <td class="ps-4 py-3 fw-semibold text-dark">{{ $key->description }}</td>
                                <td class="py-3 font-monospace small">...{{ $key->truncated_key }}</td>
                                <td class="py-3">
                                    <span class="badge bg-light text-dark border">{{ $key->permissions }}</span>
                                </td>
                                <td class="py-3 small text-muted">
                                    {{ $key->last_access_at ? $key->last_access_at->diffForHumans() : 'Sin actividad' }}
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <form action="{{ route('admin.integrations.wc.keys.revoke', $key->id) }}" method="POST" onsubmit="return confirm('¿Revocar esta clave API de WooCommerce?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Revocar Clave">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No hay claves generadas aún. Genera una para conectar Dropi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Dropi API Settings & Interactive API Tester -->
    <div class="col-lg-5">
        <!-- Dropi Settings Form -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-gear-wide-connected text-primary me-2"></i>Configuración Dropi API
                </h5>
                @if($tokenData && $tokenData['is_valid'])
                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                        <i class="bi bi-patch-check-fill"></i> Token Activo & Válido
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-muted rounded-pill px-3 py-1">Sin Token</span>
                @endif
            </div>

            @if($tokenData && $tokenData['is_valid'])
                <div class="alert alert-success border-0 bg-success-subtle p-3 rounded-3 mb-3 small">
                    <div class="d-flex align-items-center gap-2 mb-2 fw-bold text-success">
                        <i class="bi bi-shield-check fs-5"></i> ¡Autenticación con Dropi.co Verificada!
                    </div>
                    <div class="row g-2 text-dark">
                        <div class="col-6">
                            <span class="text-muted d-block">ID Usuario Dropi:</span>
                            <strong class="font-monospace">{{ $tokenData['user_id'] }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Tipo Integración:</span>
                            <strong class="text-uppercase">{{ $tokenData['integration_type'] }}</strong>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block">URL Vinculada:</span>
                            <code class="text-primary">{{ $tokenData['integration_url'] }}</code>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.integrations.dropi.settings') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="api_url" class="form-label small fw-bold">URL de la API de Dropi *</label>
                    <input type="url" name="api_url" id="api_url" class="form-control rounded-3" value="{{ old('api_url', $dropiSettings->api_url) }}" required>
                </div>

                <div class="mb-3">
                    <label for="auth_token" class="form-label small fw-bold">Token JWT de Autenticación Dropi *</label>
                    <textarea name="auth_token" id="auth_token" rows="3" class="form-control rounded-3 font-monospace small" placeholder="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...">{{ old('auth_token', $dropiSettings->auth_token) }}</textarea>
                    <small class="text-muted">Token JWT oficial vinculado con tu cuenta de Dropi.</small>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="default_carrier" class="form-label small fw-bold">Transportadora Predeterminada</label>
                        <select name="default_carrier" id="default_carrier" class="form-select rounded-3">
                            <option value="Coordinadora" {{ $dropiSettings->default_carrier === 'Coordinadora' ? 'selected' : '' }}>Coordinadora</option>
                            <option value="Servientrega" {{ $dropiSettings->default_carrier === 'Servientrega' ? 'selected' : '' }}>Servientrega</option>
                            <option value="Interrapidisimo" {{ $dropiSettings->default_carrier === 'Interrapidisimo' ? 'selected' : '' }}>Interrapidísimo</option>
                            <option value="Envia" {{ $dropiSettings->default_carrier === 'Envia' ? 'selected' : '' }}>Envía</option>
                        </select>
                    </div>

                    <div class="col-6">
                        <label for="default_markup_percent" class="form-label small fw-bold">Margen Sugerido (%)</label>
                        <input type="number" name="default_markup_percent" id="default_markup_percent" class="form-control rounded-3" min="5" max="300" value="{{ old('default_markup_percent', $dropiSettings->default_markup_percent) }}">
                    </div>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="auto_sync_orders" value="1" id="auto_sync_orders" {{ $dropiSettings->auto_sync_orders ? 'checked' : '' }}>
                    <label class="form-check-label small fw-semibold" for="auto_sync_orders">Despachar pedidos automáticamente a Dropi</label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary rounded-pill fw-semibold">
                        <i class="bi bi-save me-1"></i> Guardar Configuración Dropi
                    </button>
                </div>
            </form>
        </div>

        <!-- Interactive API Tester Tool -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                <i class="bi bi-terminal text-success me-2"></i>Probador de Endpoints WooCommerce
            </h5>
            <p class="small text-muted mb-3">
                Verifica en tiempo real que los endpoints requeridos por Dropi respondan con esquema 100% compatible con WooCommerce / WordPress.
            </p>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="testEndpoint('/wp-json')">
                    GET /wp-json
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="testEndpoint('/wp-json/wc/v3/system_status')">
                    GET /system_status
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="testEndpoint('/wp-json/wc/v3/products')">
                    GET /products
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="testEndpoint('/wp-json/wc/v3/orders')">
                    GET /orders
                </button>
            </div>

            <div id="testOutputContainer" class="d-none">
                <div class="d-flex justify-content-between align-items-center mb-1 small">
                    <span id="testStatusBadge" class="badge bg-success">HTTP 200 OK</span>
                    <span id="testEndpointName" class="font-monospace text-muted"></span>
                </div>
                <pre id="testOutputJson" class="bg-dark text-success p-3 rounded-3 small overflow-auto" style="max-height: 200px; font-size: 0.75rem;"></pre>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Generar Clave WooCommerce -->
<div class="modal fade" id="generateKeyModal" tabindex="-1" aria-labelledby="generateKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold" id="generateKeyModalLabel"><i class="bi bi-key text-primary me-2"></i>Generar Claves WooCommerce API</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.integrations.wc.keys.generate') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label for="key_description" class="form-label small fw-bold">Descripción / Nombre de la Aplicación *</label>
                        <input type="text" name="description" id="key_description" class="form-control rounded-3" required placeholder="Ej: Conexión Dropi Colombia" value="Integración Dropi.co">
                    </div>

                    <div class="mb-2">
                        <label for="permissions" class="form-label small fw-bold">Permisos de la Clave *</label>
                        <select name="permissions" id="permissions" class="form-select rounded-3">
                            <option value="read_write" selected>Lectura y Escritura (Requerido por Dropi)</option>
                            <option value="read">Solo Lectura</option>
                            <option value="write">Solo Escritura</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Generar Clave</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function testEndpoint(url) {
        const container = document.getElementById('testOutputContainer');
        const statusBadge = document.getElementById('testStatusBadge');
        const endpointName = document.getElementById('testEndpointName');
        const outputJson = document.getElementById('testOutputJson');

        container.classList.remove('d-none');
        endpointName.textContent = url;
        statusBadge.className = 'badge bg-warning text-dark';
        statusBadge.textContent = 'Consultando...';
        outputJson.textContent = 'Enviando petición HTTP...';

        try {
            const res = await fetch(url);
            const data = await res.json();
            statusBadge.className = res.ok ? 'badge bg-success' : 'badge bg-danger';
            statusBadge.textContent = `HTTP ${res.status} ${res.statusText}`;
            outputJson.textContent = JSON.stringify(data, null, 2);
        } catch (e) {
            statusBadge.className = 'badge bg-danger';
            statusBadge.textContent = 'Error de Conexión';
            outputJson.textContent = e.message;
        }
    }
</script>
@endpush
