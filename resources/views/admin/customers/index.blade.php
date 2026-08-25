@extends('layouts.admin')

@section('title', 'Gestión de Clientes')
@section('page_header', 'Directorio de Clientes & Compradores (Colombia)')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-people text-primary me-2"></i>Clientes & Compradores Registrados</h4>
        <p class="text-muted small mb-0">Gestiona los datos de contacto, cédulas DNI, direcciones de despacho y consulta el historial Dropi de tus compradores.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- Button: Consultar Detalles del Comprador (Dropi) -->
        <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 shadow-sm fw-semibold btn-sm" onclick="openBuyerDetailsModal('')">
            <i class="bi bi-shield-check me-1 text-success"></i> Detalles del Comprador (Dropi)
        </button>

        <button type="button" class="btn btn-primary rounded-pill px-3 py-2 shadow-sm fw-semibold btn-sm" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
            <i class="bi bi-person-plus-fill me-1"></i> Agregar Nuevo Cliente
        </button>
    </div>
</div>

<!-- Search Bar -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
    <form action="{{ route('admin.customers.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-9">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-start-0 rounded-end-pill" placeholder="Buscar por nombre, cédula DNI, correo, teléfono o ciudad...">
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold">Buscar Cliente</button>
            @if(request('q'))
                <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Customers Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="bg-light small text-muted text-uppercase">
                <tr>
                    <th class="ps-4 py-3">Cliente</th>
                    <th class="py-3">Cédula / DNI</th>
                    <th class="py-3">Contacto</th>
                    <th class="py-3">Ubicación (Colombia)</th>
                    <th class="py-3 text-center">Pedidos</th>
                    <th class="py-3 text-end">Total Comprado (COP)</th>
                    <th class="pe-4 py-3 text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm" style="width: 42px; height: 42px;">
                                    {{ substr($customer->name, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $customer->name }}</h6>
                                    <small class="text-muted">{{ $customer->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            @if($customer->dni)
                                <strong class="font-monospace text-dark">{{ $customer->dni }}</strong>
                            @else
                                <span class="text-muted small">No registrada</span>
                            @endif
                        </td>
                        <td class="py-3 small">
                            <div><i class="bi bi-telephone text-success me-1"></i> {{ $customer->phone ?: 'Sin teléfono' }}</div>
                        </td>
                        <td class="py-3 small">
                            <div class="fw-semibold text-dark">{{ $customer->city ?: 'Colombia' }}</div>
                            <div class="text-muted">{{ $customer->address ?: 'Sin dirección' }}</div>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                {{ $customer->orders_count }} {{ Str::plural('pedido', $customer->orders_count) }}
                            </span>
                        </td>
                        <td class="py-3 text-end fw-bold text-primary font-monospace">
                            {{ format_cop($customer->orders_sum_total ?: 0) }}
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-primary fw-semibold" 
                                    onclick="openBuyerDetailsModal('{{ $customer->phone }}')" title="Consultar historial Dropi">
                                <i class="bi bi-shield-check text-success me-1"></i> Historial Dropi
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 text-muted d-block mb-2"></i>
                            No se encontraron clientes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center">
    {{ $customers->links('pagination::bootstrap-5') }}
</div>

<!-- ========================================================================= -->
<!-- MODAL: DETALLES DEL COMPRADOR (DROPI API) -->
<!-- ========================================================================= -->
<div class="modal fade" id="dropiBuyerDetailsModal" tabindex="-1" aria-labelledby="dropiBuyerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="dropiBuyerDetailsModalLabel">
                        <i class="bi bi-person-badge-fill text-primary me-2"></i>Detalles del comprador
                    </h5>
                    <p class="text-muted small mb-0 mt-1">
                        Ingresa el número de teléfono del comprador para consultar su historial de compras.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <!-- Phone Lookup Form -->
                <form id="buyerLookupForm" onsubmit="event.preventDefault(); fetchBuyerDetails();" class="mb-4">
                    <label class="form-label small fw-bold text-dark mb-1">Número de teléfono del Comprador *</label>
                    <div class="input-group mb-2">
                        <!-- Flag / Country Code Prefix -->
                        <span class="input-group-text bg-light border border-end-0 rounded-start-3 px-3 d-flex align-items-center gap-1">
                            <span class="fs-5" title="Colombia">🇨🇴</span>
                            <span class="fw-bold font-monospace text-dark ms-1">57</span>
                        </span>
                        <!-- Phone Input -->
                        <input type="tel" id="buyerPhoneInput" class="form-control form-control-lg border font-monospace fw-bold fs-6" 
                               placeholder="Número de teléfono (ej: 3129876543)" required autofocus>
                        <!-- Submit Button -->
                        <button class="btn btn-primary px-4 fw-bold rounded-end-3" type="submit" id="buyerSearchBtn">
                            <i class="bi bi-search me-1"></i> Consultar Historial
                        </button>
                    </div>

                    <!-- Default Helper Notice -->
                    <div class="text-muted small d-flex align-items-center gap-1" id="buyerPhoneHelpText">
                        <i class="bi bi-info-circle text-primary"></i>
                        <span>Debes ingresar un número de celular valido para poder ver el historial del comprador</span>
                    </div>
                </form>

                <!-- Loading State -->
                <div id="buyerLoadingState" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Consultando historial Dropi...</span>
                    </div>
                    <p class="text-muted small mt-2 mb-0">Consultando historial de compras y confiabilidad en Dropi...</p>
                </div>

                <!-- Error Alert -->
                <div id="buyerErrorAlert" class="alert alert-danger border-0 rounded-3 p-3 d-none mb-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <span id="buyerErrorMessage">Debes ingresar un número de celular válido para poder ver el historial del comprador</span>
                    </div>
                </div>

                <!-- Buyer Results Container -->
                <div id="buyerResultsContainer" class="d-none">
                    <!-- Top Buyer Score & Reliability Banner -->
                    <div class="p-3 rounded-4 mb-4 border d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" id="buyerScoreBanner">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle p-3 d-flex align-items-center justify-content-center fs-3" id="buyerScoreIconWrapper" style="width: 54px; height: 54px;">
                                <i class="bi bi-shield-check" id="buyerScoreIcon"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 text-dark" id="buyerNameDisplay"></h6>
                                    <span class="badge rounded-pill px-3 py-1 small" id="buyerScoreBadge"></span>
                                </div>
                                <small class="text-muted" id="buyerPhoneDisplay"></small>
                            </div>
                        </div>

                        <div class="text-md-end">
                            <div class="small text-muted mb-0">Tasa de Entrega Confiable</div>
                            <h4 class="fw-bold font-monospace mb-0" id="buyerReliabilityScore"></h4>
                        </div>
                    </div>

                    <!-- Risk Assessment Alert -->
                    <div class="alert alert-light border shadow-sm rounded-3 p-3 mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-lightbulb-fill text-warning fs-5"></i>
                        <span class="small fw-semibold text-dark" id="buyerRiskAssessment"></span>
                    </div>

                    <!-- Stats KPI Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center border">
                                <span class="text-muted small d-block">Pedidos Totales</span>
                                <h4 class="fw-bold text-dark mb-0 font-monospace" id="buyerTotalOrders">0</h4>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-success-subtle p-3 rounded-3 text-center border border-success border-opacity-25">
                                <span class="text-success small d-block fw-semibold">Entregados</span>
                                <h4 class="fw-bold text-success mb-0 font-monospace" id="buyerDeliveredOrders">0</h4>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-info-subtle p-3 rounded-3 text-center border border-info border-opacity-25">
                                <span class="text-info small d-block fw-semibold">En Proceso / Tránsito</span>
                                <h4 class="fw-bold text-info mb-0 font-monospace" id="buyerProcessingOrders">0</h4>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-danger-subtle p-3 rounded-3 text-center border border-danger border-opacity-25">
                                <span class="text-danger small d-block fw-semibold">Devoluciones / Canc.</span>
                                <h4 class="fw-bold text-danger mb-0 font-monospace" id="buyerCancelledOrders">0</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Buyer Details & Location -->
                    <div class="card border bg-light rounded-3 p-3 mb-4">
                        <div class="row g-3 small">
                            <div class="col-sm-6">
                                <span class="text-muted d-block">Correo Electrónico:</span>
                                <strong class="text-dark" id="buyerEmailDisplay">No registrado</strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block">Total Facturado (COP):</span>
                                <strong class="text-primary font-monospace fs-6" id="buyerTotalSpentDisplay">$ 0 COP</strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block">Ciudad de Envío:</span>
                                <strong class="text-dark" id="buyerCityDisplay">Colombia</strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block">Dirección Registrada:</span>
                                <strong class="text-dark" id="buyerAddressDisplay">N/A</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Table Container -->
                    <h6 class="fw-bold text-dark mb-2">
                        <i class="bi bi-clock-history text-primary me-1"></i> Historial Reciente de Pedidos
                    </h6>
                    <div class="table-responsive border rounded-3 bg-white" style="max-height: 220px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0 small">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="ps-3 py-2">Nº Pedido</th>
                                    <th class="py-2">Fecha</th>
                                    <th class="py-2">Total (COP)</th>
                                    <th class="py-2">Ciudad</th>
                                    <th class="pe-3 py-2 text-end">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="buyerOrdersTableBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-4 py-3 bg-light">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal: Registrar Nuevo Cliente -->
<!-- ========================================================================= -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold" id="newCustomerModalLabel"><i class="bi bi-person-plus text-primary me-2"></i>Registrar Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.customers.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-bold">Nombre Completo *</label>
                        <input type="text" name="name" id="name" class="form-control rounded-3" required placeholder="Ej: Marcela Trujillo">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="dni" class="form-label small fw-bold">Cédula / DNI *</label>
                            <input type="text" name="dni" id="dni" class="form-control rounded-3" required placeholder="Ej: 52890123">
                        </div>
                        <div class="col-6">
                            <label for="phone" class="form-label small fw-bold">Celular / WhatsApp *</label>
                            <input type="tel" name="phone" id="phone" class="form-control rounded-3" required placeholder="+57 312 987 6543">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-bold">Correo Electrónico *</label>
                        <input type="email" name="email" id="email" class="form-control rounded-3" required placeholder="marcela@correo.com">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="city" class="form-label small fw-bold">Ciudad *</label>
                            <input type="text" name="city" id="city" class="form-control rounded-3" required placeholder="Ej: Bucaramanga">
                        </div>
                        <div class="col-6">
                            <label for="department" class="form-label small fw-bold">Departamento</label>
                            <input type="text" name="department" id="department" class="form-control rounded-3" placeholder="Ej: Santander">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label small fw-bold">Dirección de Entrega *</label>
                        <input type="text" name="address" id="address" class="form-control rounded-3" required placeholder="Calle 45 # 28-10, Apto 502">
                    </div>

                    <div class="mb-2">
                        <label for="postal_code" class="form-label small fw-bold">Código Postal</label>
                        <input type="text" name="postal_code" id="postal_code" class="form-control rounded-3" placeholder="680002">
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openBuyerDetailsModal(phone) {
    const input = document.getElementById('buyerPhoneInput');
    const modalEl = document.getElementById('dropiBuyerDetailsModal');
    const modal = new bootstrap.Modal(modalEl);

    // Reset view
    document.getElementById('buyerErrorAlert').classList.add('d-none');
    document.getElementById('buyerResultsContainer').classList.add('d-none');
    document.getElementById('buyerLoadingState').classList.add('d-none');

    if (phone) {
        // Clean and fill phone
        input.value = phone.replace(/[^0-9]/g, '');
        modal.show();
        setTimeout(() => fetchBuyerDetails(), 200);
    } else {
        input.value = '';
        modal.show();
    }
}

function fetchBuyerDetails() {
    const phoneInput = document.getElementById('buyerPhoneInput');
    const phone = phoneInput.value.trim();

    const loading = document.getElementById('buyerLoadingState');
    const errorAlert = document.getElementById('buyerErrorAlert');
    const errorMessage = document.getElementById('buyerErrorMessage');
    const results = document.getElementById('buyerResultsContainer');
    const searchBtn = document.getElementById('buyerSearchBtn');

    if (!phone || phone.length < 7) {
        errorAlert.classList.remove('d-none');
        errorMessage.textContent = 'Debes ingresar un número de celular valido para poder ver el historial del comprador';
        results.classList.add('d-none');
        return;
    }

    // Show loading
    errorAlert.classList.add('d-none');
    results.classList.add('d-none');
    loading.classList.remove('d-none');
    searchBtn.disabled = true;

    fetch(`{{ route('admin.customers.buyer_details') }}?phone=${encodeURIComponent(phone)}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        loading.classList.add('d-none');
        searchBtn.disabled = false;

        if (!data.success) {
            errorAlert.classList.remove('d-none');
            errorMessage.textContent = data.message || 'Debes ingresar un número de celular valido para poder ver el historial del comprador';
            return;
        }

        // Render Results
        results.classList.remove('d-none');
        document.getElementById('buyerNameDisplay').textContent = data.name;
        document.getElementById('buyerPhoneDisplay').textContent = data.phone;
        document.getElementById('buyerEmailDisplay').textContent = data.email;
        document.getElementById('buyerCityDisplay').textContent = data.city;
        document.getElementById('buyerAddressDisplay').textContent = data.address;
        document.getElementById('buyerTotalSpentDisplay').textContent = data.total_spent;

        document.getElementById('buyerTotalOrders').textContent = data.total_orders;
        document.getElementById('buyerDeliveredOrders').textContent = data.delivered_orders;
        document.getElementById('buyerProcessingOrders').textContent = data.processing_orders;
        document.getElementById('buyerCancelledOrders').textContent = data.cancelled_orders;

        document.getElementById('buyerReliabilityScore').textContent = data.reliability_score + '%';
        document.getElementById('buyerRiskAssessment').textContent = data.risk_assessment;

        // Score Badge and styling
        const badge = document.getElementById('buyerScoreBadge');
        const iconWrapper = document.getElementById('buyerScoreIconWrapper');
        const scoreBanner = document.getElementById('buyerScoreBanner');

        badge.textContent = data.reliability_label;
        badge.className = `badge bg-${data.reliability_class} text-white rounded-pill px-3 py-1 small shadow-sm`;
        
        iconWrapper.className = `rounded-circle p-3 d-flex align-items-center justify-content-center fs-3 bg-${data.reliability_class}-subtle text-${data.reliability_class}`;
        scoreBanner.className = `p-3 rounded-4 mb-4 border border-${data.reliability_class} border-opacity-25 bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3`;

        // Render Orders Table
        const tbody = document.getElementById('buyerOrdersTableBody');
        tbody.innerHTML = '';

        if (data.orders && data.orders.length > 0) {
            data.orders.forEach(ord => {
                const statusBadge = ord.status === 'delivered' ? 'bg-success' : (ord.status === 'cancelled' ? 'bg-danger' : 'bg-primary');
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="ps-3 py-2 fw-bold text-dark">${ord.order_number}</td>
                    <td class="py-2 text-muted">${ord.date}</td>
                    <td class="py-2 fw-bold font-monospace text-primary">${ord.total}</td>
                    <td class="py-2 text-dark">${ord.city || 'N/A'}</td>
                    <td class="pe-3 py-2 text-end">
                        <span class="badge ${statusBadge} rounded-pill px-2.5 py-1 small">${ord.status_label}</span>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td colspan="5" class="text-center py-3 text-muted">
                    No registra pedidos previos en tienda. Usuario nuevo verificado en Colombia.
                </td>
            `;
            tbody.appendChild(tr);
        }
    })
    .catch(err => {
        loading.classList.add('d-none');
        searchBtn.disabled = false;
        errorAlert.classList.remove('d-none');
        errorMessage.textContent = 'Error al conectar con el servicio de verificación de compradores.';
    });
}
</script>
@endsection
