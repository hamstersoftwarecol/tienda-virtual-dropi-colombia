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
        <button type="button" class="btn btn-primary rounded-pill px-3 py-2 shadow-sm fw-semibold btn-sm d-flex align-items-center gap-1.5" onclick="openBuyerDetailsModal('')">
            <i class="bi bi-shield-check text-white"></i> Detalles del Comprador (Dropi)
        </button>

        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 shadow-sm fw-semibold btn-sm d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
            <i class="bi bi-person-plus-fill"></i> Agregar Nuevo Cliente
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
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-primary fw-semibold shadow-2xs" 
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
<!-- MODAL: DETALLES DEL COMPRADOR (DROPI API OFICIAL) -->
<!-- ========================================================================= -->
<div class="modal fade" id="dropiBuyerDetailsModal" tabindex="-1" aria-labelledby="dropiBuyerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden bg-white">
            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="dropiBuyerDetailsModalLabel">
                        Detalles del comprador
                    </h5>
                    <p class="text-muted small mb-0 mt-1">
                        Ingresa el número de teléfono del comprador para consultar su historial de compras.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-light">
                <!-- Phone Lookup Form -->
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
                    <form id="buyerLookupForm" onsubmit="event.preventDefault(); fetchBuyerDetails();">
                        <label class="form-label small fw-bold text-dark mb-2">Número de teléfono</label>
                        <div class="input-group mb-2">
                            <!-- Flag + 57 Prefix -->
                            <span class="input-group-text bg-white border border-end-0 rounded-start-3 px-3 d-flex align-items-center gap-1.5">
                                <span class="fs-5">🇨🇴</span>
                                <span class="fw-bold font-monospace text-dark ms-1">57</span>
                            </span>
                            <!-- Phone Input -->
                            <input type="tel" id="buyerPhoneInput" class="form-control form-control-lg border font-monospace fw-bold fs-6" 
                                   placeholder="Número de teléfono" required autofocus>
                            <!-- Submit Button -->
                            <button class="btn btn-primary px-4 fw-bold rounded-end-3" type="submit" id="buyerSearchBtn">
                                <i class="bi bi-search me-1"></i> Consultar
                            </button>
                        </div>

                        <!-- Default Notice -->
                        <div class="text-muted small d-flex align-items-center gap-1.5" id="buyerPhoneHelpText">
                            <i class="bi bi-info-circle text-primary"></i>
                            <span>Debes ingresar un número de celular valido para poder ver el historial del comprador</span>
                        </div>
                    </form>
                </div>

                <!-- Loading State -->
                <div id="buyerLoadingState" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Consultando historial Dropi...</span>
                    </div>
                    <p class="text-muted small mt-2 mb-0 fw-semibold">Consultando detalles del comprador en Dropi...</p>
                </div>

                <!-- Error Alert -->
                <div id="buyerErrorAlert" class="alert alert-danger border-0 rounded-3 p-3 d-none mb-0 shadow-sm">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <span id="buyerErrorMessage">Debes ingresar un número de celular valido para poder ver el historial del comprador</span>
                    </div>
                </div>

                <!-- No History Found State -->
                <div id="buyerNoHistoryContainer" class="d-none">
                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white mb-3">
                        <div class="mx-auto mb-3" style="width: 64px; height: 64px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-person-x text-muted fs-2"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Sin historial registrado en Dropi</h6>
                        <p class="text-muted small mb-0" id="buyerNoHistoryMessage">
                            No se encontró historial de compras para este número de teléfono.
                        </p>
                    </div>
                </div>

                <!-- Buyer Results Container -->
                <div id="buyerResultsContainer" class="d-none">
                    <!-- Top Buyer Profile Banner -->
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="text-muted small fw-bold">Comprador</span>
                                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small fw-bold" id="buyerTypeBadge">
                                        Esporádico
                                    </span>
                                </div>
                                <h4 class="fw-bold font-monospace text-dark mb-0 d-flex align-items-center gap-2">
                                    <span id="buyerPhoneDisplay">3103761814</span>
                                </h4>
                            </div>
                            <div class="text-md-end">
                                <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 small">
                                    <i class="bi bi-calendar3 me-1"></i> Última actualización diaria: <strong id="buyerLastUpdate" class="text-dark">23 Ago 2026</strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- En tu tienda vs En otras tiendas -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                                <span class="text-muted small d-block mb-1">En tu tienda</span>
                                <h2 class="fw-bold text-dark font-monospace mb-0" id="buyerInStoreOrders">0</h2>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                                <span class="text-muted small d-block mb-1">En otras tiendas</span>
                                <h2 class="fw-bold text-primary font-monospace mb-0" id="buyerInOtherStoresOrders">1</h2>
                            </div>
                        </div>
                    </div>

                    <!-- Probabilidad de entrega -->
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">Probabilidad de entrega</h6>
                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3 py-1 fw-bold fs-6" id="buyerDeliveryProbability">
                                Segura
                            </span>
                        </div>
                        <p class="text-dark small mb-1 fw-semibold" id="buyerDeliveryCertainty">
                            Alta certeza de entrega sin inconvenientes.
                        </p>
                        <p class="text-muted small mb-3" id="buyerDeliveryAction">
                            Monitorear el proceso de entrega.
                        </p>

                        <!-- Progress Bar -->
                        <div class="d-flex justify-content-between align-items-center small mb-1.5">
                            <span class="text-muted">Entregadas</span>
                            <strong class="text-success font-monospace" id="buyerDeliveredMetric">1 (100%)</strong>
                        </div>
                        <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar bg-success rounded-pill" id="buyerProgressBar" role="progressbar" style="width: 100%;"></div>
                        </div>
                    </div>

                    <!-- Reportes Negativos e Incidencias (Dropi Risk System) -->
                    <div id="buyerNegativeReportsSection" class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-success fs-5" id="negativeReportIcon"></i>
                                <h6 class="fw-bold text-dark mb-0">Reportes Negativos e Incidencias</h6>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 small fw-bold" id="negativeReportsBadge">
                                0 Reportes
                            </span>
                        </div>
                        
                        <!-- When negative reports exist -->
                        <div id="negativeReportsListContainer" class="d-none mt-2">
                            <p class="text-danger small mb-2 fw-semibold">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Se registraron devoluciones e incidencias de entrega previas para este comprador:
                            </p>
                            <div id="negativeReportsList" class="d-flex flex-column gap-2">
                                <!-- Dynamically populated -->
                            </div>
                        </div>

                        <!-- Clean state (0 negative reports) -->
                        <div id="negativeReportsCleanState" class="d-flex align-items-center gap-2 p-2.5 rounded-3 bg-success-subtle border border-success border-opacity-25 small text-success fw-semibold mt-1">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <span>Este comprador no tiene reportes negativos ni devoluciones registradas en Dropi.</span>
                        </div>
                    </div>

                    <!-- Análisis detallado -->
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0">Análisis detallado</h6>
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="text-muted small">Filtrar por:</span>
                                <select class="form-select form-select-sm rounded-pill border py-0 px-2 small" style="width: auto; font-size: 0.8rem;">
                                    <option>Todo el historial</option>
                                </select>
                            </div>
                        </div>

                        <!-- Summary Counter Pills -->
                        <div class="row g-2 text-center mb-3">
                            <div class="col-3">
                                <div class="bg-light p-2 rounded-3 border">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Todas</small>
                                    <strong class="font-monospace text-dark fs-6" id="buyerTotalAll">1</strong>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="bg-light p-2 rounded-3 border">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">En tránsito</small>
                                    <strong class="font-monospace text-info fs-6" id="buyerTotalInTransit">0</strong>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="bg-light p-2 rounded-3 border">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Devoluciones</small>
                                    <strong class="font-monospace text-danger fs-6" id="buyerTotalReturns">0</strong>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="bg-light p-2 rounded-3 border">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Entregadas</small>
                                    <strong class="font-monospace text-success fs-6" id="buyerTotalDelivered">1</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Desglose por Transportadora -->
                        <div class="mb-3 border-top pt-2.5">
                            <small class="text-muted fw-bold d-block mb-1.5">Transportadora</small>
                            <div id="buyerCarriersList">
                                <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded-3 border small">
                                    <strong class="text-dark">TCC</strong>
                                    <div class="font-monospace small">
                                        <span class="text-muted">0 En tránsito</span> / 
                                        <span class="text-muted">0 Devoluciones</span> / 
                                        <span class="text-success fw-bold">1 Entregas</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desglose por Tipo de envío -->
                        <div class="mb-3 border-top pt-2.5">
                            <small class="text-muted fw-bold d-block mb-1.5">Tipo de envío</small>
                            <div id="buyerShippingTypeList">
                                <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded-3 border small">
                                    <strong class="text-dark">Contra entrega</strong>
                                    <div class="font-monospace small">
                                        <span class="text-muted">0 En tránsito</span> / 
                                        <span class="text-muted">0 Devoluciones</span> / 
                                        <span class="text-success fw-bold">1 Entregas</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desglose por Comportamiento por precio -->
                        <div class="border-top pt-2.5">
                            <small class="text-muted fw-bold d-block mb-1.5">Comportamiento por precio</small>
                            <div id="buyerPriceBehaviorList">
                                <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded-3 border small">
                                    <strong class="text-dark">$50.001 a $100.000</strong>
                                    <div class="font-monospace small">
                                        <span class="text-muted">0 En tránsito</span> / 
                                        <span class="text-muted">0 Devoluciones</span> / 
                                        <span class="text-success fw-bold">1 Entregas</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-4 py-3 bg-white">
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
    document.getElementById('buyerNoHistoryContainer').classList.add('d-none');
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
    const noHistoryContainer = document.getElementById('buyerNoHistoryContainer');
    const noHistoryMessage = document.getElementById('buyerNoHistoryMessage');
    const results = document.getElementById('buyerResultsContainer');
    const searchBtn = document.getElementById('buyerSearchBtn');

    if (!phone || phone.length < 7) {
        errorAlert.classList.remove('d-none');
        errorMessage.textContent = 'Debes ingresar un número de celular valido para poder ver el historial del comprador';
        results.classList.add('d-none');
        noHistoryContainer.classList.add('d-none');
        return;
    }

    // Show loading
    errorAlert.classList.add('d-none');
    results.classList.add('d-none');
    noHistoryContainer.classList.add('d-none');
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

        // If no purchase history found for this phone
        if (data.has_history === false) {
            noHistoryContainer.classList.remove('d-none');
            noHistoryMessage.textContent = data.message || 'No se encontró historial de compras para este número de teléfono.';
            results.classList.add('d-none');
            return;
        }

        // Render Results
        results.classList.remove('d-none');
        noHistoryContainer.classList.add('d-none');
        document.getElementById('buyerPhoneDisplay').textContent = data.phone;
        document.getElementById('buyerTypeBadge').textContent = data.buyer_type || 'Esporádico';
        document.getElementById('buyerLastUpdate').textContent = data.last_update || '23 Ago 2026';

        // Stores
        document.getElementById('buyerInStoreOrders').textContent = data.in_store_orders;
        document.getElementById('buyerInOtherStoresOrders').textContent = data.in_other_stores_orders;

        // Delivery Probability
        const probBadge = document.getElementById('buyerDeliveryProbability');
        probBadge.textContent = data.delivery_probability;
        probBadge.className = `badge bg-${data.delivery_probability_class}-subtle text-${data.delivery_probability_class} border border-${data.delivery_probability_class} border-opacity-25 rounded-pill px-3 py-1 fw-bold fs-6`;

        document.getElementById('buyerDeliveryCertainty').textContent = data.delivery_certainty;
        document.getElementById('buyerDeliveryAction').textContent = data.delivery_action;
        document.getElementById('buyerDeliveredMetric').textContent = `${data.delivered_count} (${data.delivered_percent}%)`;
        document.getElementById('buyerProgressBar').style.width = data.delivered_percent + '%';
        document.getElementById('buyerProgressBar').className = `progress-bar bg-${data.delivery_probability_class} rounded-pill`;

        // Render Negative Reports (Incidencias y Devoluciones)
        const negIcon = document.getElementById('negativeReportIcon');
        const negBadge = document.getElementById('negativeReportsBadge');
        const negListContainer = document.getElementById('negativeReportsListContainer');
        const negList = document.getElementById('negativeReportsList');
        const negCleanState = document.getElementById('negativeReportsCleanState');

        const negativeCount = data.negative_reports_count || 0;
        negBadge.textContent = `${negativeCount} ${negativeCount === 1 ? 'Reporte' : 'Reportes'}`;

        if (negativeCount > 0 && data.negative_reports && data.negative_reports.length > 0) {
            negIcon.className = 'bi bi-shield-exclamation text-danger fs-5';
            negBadge.className = 'badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 small fw-bold';
            negListContainer.classList.remove('d-none');
            negCleanState.classList.add('d-none');

            negList.innerHTML = '';
            data.negative_reports.forEach(r => {
                const item = document.createElement('div');
                item.className = 'p-2.5 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 text-start';
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-danger text-white rounded-pill px-2 py-0.5 small fw-bold">
                            <i class="bi bi-x-circle me-1"></i> ${r.carrier || 'Transportadora'}
                        </span>
                        <span class="text-muted small font-monospace">${r.date || ''}</span>
                    </div>
                    <div class="text-dark small fw-semibold mb-1">${r.reason || 'Incidencia de entrega / Devolución'}</div>
                    <div class="d-flex align-items-center gap-2 small text-muted" style="font-size: 0.75rem;">
                        <span><i class="bi bi-shop me-1"></i>${r.store_type || 'Red Dropi'}</span>
                        <span>•</span>
                        <span class="text-danger fw-bold">Severidad: ${r.severity || 'Alta'}</span>
                    </div>
                `;
                negList.appendChild(item);
            });
        } else {
            negIcon.className = 'bi bi-shield-check text-success fs-5';
            negBadge.className = 'badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 small fw-bold';
            negListContainer.classList.add('d-none');
            negCleanState.classList.remove('d-none');
        }

        // Summary Counters
        document.getElementById('buyerTotalAll').textContent = data.total_history;
        document.getElementById('buyerTotalInTransit').textContent = data.in_transit_count;
        document.getElementById('buyerTotalReturns').textContent = data.returns_count;
        document.getElementById('buyerTotalDelivered').textContent = data.delivered_count;

        // Render Carriers List
        const carriersContainer = document.getElementById('buyerCarriersList');
        carriersContainer.innerHTML = '';
        if (data.carriers_breakdown && data.carriers_breakdown.length > 0) {
            data.carriers_breakdown.forEach(c => {
                const row = document.createElement('div');
                row.className = 'd-flex justify-content-between align-items-center bg-light p-2 rounded-3 border small mb-1.5';
                row.innerHTML = `
                    <strong class="text-dark">${c.name}</strong>
                    <div class="font-monospace small">
                        <span class="text-muted">${c.in_transit} En tránsito</span> / 
                        <span class="text-muted">${c.returns} Devoluciones</span> / 
                        <span class="text-success fw-bold">${c.delivered} Entregas</span>
                    </div>
                `;
                carriersContainer.appendChild(row);
            });
        }

        // Render Shipping Type List
        const shippingTypeContainer = document.getElementById('buyerShippingTypeList');
        shippingTypeContainer.innerHTML = '';
        if (data.shipping_type_breakdown && data.shipping_type_breakdown.length > 0) {
            data.shipping_type_breakdown.forEach(st => {
                const row = document.createElement('div');
                row.className = 'd-flex justify-content-between align-items-center bg-light p-2 rounded-3 border small mb-1.5';
                row.innerHTML = `
                    <strong class="text-dark">${st.name}</strong>
                    <div class="font-monospace small">
                        <span class="text-muted">${st.in_transit} En tránsito</span> / 
                        <span class="text-muted">${st.returns} Devoluciones</span> / 
                        <span class="text-success fw-bold">${st.delivered} Entregas</span>
                    </div>
                `;
                shippingTypeContainer.appendChild(row);
            });
        }

        // Render Price Behavior List
        const priceBehaviorContainer = document.getElementById('buyerPriceBehaviorList');
        priceBehaviorContainer.innerHTML = '';
        if (data.price_behavior_breakdown && data.price_behavior_breakdown.length > 0) {
            data.price_behavior_breakdown.forEach(pb => {
                const row = document.createElement('div');
                row.className = 'd-flex justify-content-between align-items-center bg-light p-2 rounded-3 border small mb-1.5';
                row.innerHTML = `
                    <strong class="text-dark">${pb.range}</strong>
                    <div class="font-monospace small">
                        <span class="text-muted">${pb.in_transit} En tránsito</span> / 
                        <span class="text-muted">${pb.returns} Devoluciones</span> / 
                        <span class="text-success fw-bold">${pb.delivered} Entregas</span>
                    </div>
                `;
                priceBehaviorContainer.appendChild(row);
            });
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
