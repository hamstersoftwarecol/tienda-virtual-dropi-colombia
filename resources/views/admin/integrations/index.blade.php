@extends('layouts.admin')

@section('title', 'Integración WooCommerce REST API')
@section('page_header', 'Centro de Integraciones: WooCommerce REST API')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-plug-fill text-primary me-2"></i>Conexión WooCommerce REST API</h4>
    <p class="text-muted small mb-0">
        Nuestra plataforma incluye emulación nativa de la <strong>REST API de WooCommerce (WordPress v3)</strong> para conectar cualquier aplicación externa, ERP o plataforma de dropshipping usando credenciales seguras Consumer Key y Consumer Secret.
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
                <h5 class="fw-bold mb-0 text-success">¡Nueva Clave de API Generada con Éxito!</h5>
                <small class="text-muted">Copia estas credenciales ahora. Por seguridad, el Consumer Secret no se volverá a mostrar completo.</small>
            </div>
        </div>

        <div class="bg-white p-3 rounded-3 border mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="small text-muted fw-bold">Consumer Key (CK):</label>
                    <div class="input-group input-group-sm mt-1">
                        <input type="text" class="form-control font-monospace bg-light" value="{{ $newKey['consumer_key'] }}" id="newCK" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('newCK').value); alert('Consumer Key copiada al portapapeles');"><i class="bi bi-clipboard"></i></button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="small text-muted fw-bold">Consumer Secret (CS):</label>
                    <div class="input-group input-group-sm mt-1">
                        <input type="text" class="form-control font-monospace bg-light" value="{{ $newKey['consumer_secret'] }}" id="newCS" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('newCS').value); alert('Consumer Secret copiada al portapapeles');"><i class="bi bi-clipboard"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row g-4">
    <!-- Left Column: Instructions & Connection Details -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-wordpress text-primary me-2"></i>Datos de Conexión REST API
                </h5>
                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                    <i class="bi bi-check-circle-fill"></i> API v3 Activa
                </span>
            </div>

            <p class="small text-secondary mb-3">
                Para conectar tu tienda a cualquier plataforma externa compatible con WooCommerce, utiliza los siguientes parámetros:
            </p>

            <div class="bg-light p-3 rounded-3 border mb-3 small">
                <div class="mb-3">
                    <span class="text-muted d-block fw-bold mb-1">URL de la Tienda (Website URL):</span>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control font-monospace bg-white" value="{{ url('/') }}" id="storeUrl" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('storeUrl').value); alert('URL copiada');"><i class="bi bi-clipboard"></i> Copiar</button>
                    </div>
                </div>

                <div>
                    <span class="text-muted d-block fw-bold mb-1">Endpoint REST API:</span>
                    <code class="text-primary fs-6">{{ url('/wp-json/wc/v3') }}</code>
                </div>
            </div>

            <div class="small text-muted">
                <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-check2-circle text-primary me-1"></i>Endpoints Soportados:</h6>
                <ul class="ps-3 mb-0">
                    <li><code>GET /wp-json/wc/v3/system_status</code></li>
                    <li><code>GET, POST, PUT, DELETE /wp-json/wc/v3/products</code></li>
                    <li><code>GET, POST, PUT /wp-json/wc/v3/orders</code></li>
                    <li><code>GET /wp-json/wc/v3/customers</code></li>
                    <li><code>POST /wp-json/wc/v3/webhooks</code></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Right Column: API Keys Table -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">Claves API Activas ({{ $apiKeys->count() }})</h5>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#generateKeyModal">
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
                                    <form action="{{ route('admin.integrations.wc.keys.revoke', $key->id) }}" method="POST" onsubmit="return confirm('¿Revocar y eliminar esta clave API de WooCommerce?');">
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
                                <td colspan="5" class="text-center py-4 text-muted">No hay claves de API creadas. Genera una clave para conectar aplicaciones externas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Generar Nueva Clave WooCommerce API -->
<div class="modal fade" id="generateKeyModal" tabindex="-1" aria-labelledby="generateKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark" id="generateKeyModalLabel">
                    <i class="bi bi-key text-primary me-2"></i>Generar Clave WooCommerce REST API
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.integrations.wc.keys.generate') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label for="description" class="form-label small fw-bold">Descripción / Nombre de la Aplicación *</label>
                        <input type="text" name="description" id="description" class="form-control rounded-3" placeholder="Ej: Conexión Dropshipping, App Móvil, ERP" required>
                    </div>

                    <div class="mb-3">
                        <label for="permissions" class="form-label small fw-bold">Permisos de la Clave *</label>
                        <select name="permissions" id="permissions" class="form-select rounded-3" required>
                            <option value="read_write" selected>Lectura / Escritura (Recomendado)</option>
                            <option value="read">Solo Lectura (Read Only)</option>
                            <option value="write">Solo Escritura (Write Only)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-shield-lock me-1"></i> Generar Claves
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
