@extends('layouts.admin')

@section('title', 'Gestión de Clientes')
@section('page_header', 'Directorio de Clientes & Compradores (Colombia)')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-people text-primary me-2"></i>Clientes & Compradores Registrados</h4>
        <p class="text-muted small mb-0">Gestiona los datos de contacto, cédulas DNI y direcciones de despacho de tus compradores.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" class="btn btn-primary rounded-pill px-3 py-2 shadow-sm fw-semibold btn-sm d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
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
                    <th class="pe-4 py-3 text-end">Total Comprado (COP)</th>
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
                        <td class="pe-4 py-3 text-end fw-bold text-primary font-monospace">
                            {{ format_cop($customer->orders_sum_total ?: 0) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
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
@endsection
