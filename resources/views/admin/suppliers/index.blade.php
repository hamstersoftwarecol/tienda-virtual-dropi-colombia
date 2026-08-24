@extends('layouts.admin')

@section('title', 'Proveedores & Bodegas')
@section('page_header', 'Proveedores & Bodegas Dropi (Colombia)')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Bodegas y Proveedores Verificados</h4>
        <p class="text-muted small mb-0">Explora bodegas mayoristas en Colombia y sus catálogos disponibles para importación y dropshipping.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.dropi.catalog') }}" class="btn btn-outline-primary rounded-pill px-3">
            <i class="bi bi-cloud-arrow-down me-1"></i> Ir al Importador Dropi
        </a>
        <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#newSupplierModal">
            <i class="bi bi-plus-lg me-1"></i> Registrar Proveedor
        </button>
    </div>
</div>

<!-- Search & Filters -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
    <form action="{{ route('admin.suppliers.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-9">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Buscar por nombre de bodega, ciudad o departamento (Ej: Medellín, Bogotá, Cali)...">
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100">Buscar Proveedor</button>
            @if(request('q'))
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Suppliers Grid -->
<div class="row g-4 mb-4">
    @forelse($suppliers as $supplier)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100 d-flex flex-column hover-lift" style="transition: transform 0.2s, box-shadow 0.2s;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $supplier->logo }}" alt="{{ $supplier->name }}" class="rounded-4 object-fit-cover shadow-sm border" style="width: 60px; height: 60px;">
                    <div class="flex-grow-1 overflow-hidden">
                        <h5 class="fw-bold mb-0 text-truncate text-dark">{{ $supplier->name }}</h5>
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <i class="bi bi-geo-alt-fill text-danger"></i>
                            <span>{{ $supplier->city }}, {{ $supplier->department }}</span>
                        </div>
                    </div>
                    @if($supplier->is_verified)
                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1" title="Verificado por Dropi">
                            <i class="bi bi-patch-check-fill"></i> Verificado
                        </span>
                    @endif
                </div>

                <p class="small text-muted mb-3 flex-grow-1">
                    {{ $supplier->description ?: 'Proveedor mayorista de productos de alta rotación para dropshipping en Colombia.' }}
                </p>

                <!-- Stats -->
                <div class="bg-light rounded-3 p-3 mb-3 small d-flex justify-content-between text-center border">
                    <div>
                        <span class="text-muted d-block" style="font-size: 0.75rem;">Calificación</span>
                        <strong class="text-warning"><i class="bi bi-star-fill"></i> {{ number_format($supplier->rating, 1) }}</strong>
                    </div>
                    <div class="border-start ps-3">
                        <span class="text-muted d-block" style="font-size: 0.75rem;">Catálogo Dropi</span>
                        <strong class="text-primary">{{ $supplier->catalog_products_count }} productos</strong>
                    </div>
                    <div class="border-start ps-3">
                        <span class="text-muted d-block" style="font-size: 0.75rem;">Importados</span>
                        <strong class="text-dark">{{ $supplier->products_count }} en tienda</strong>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-auto">
                    <a href="{{ route('admin.suppliers.show', $supplier->id) }}" class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1 fw-semibold">
                        <i class="bi bi-box-seam me-1"></i> Ver Catálogo
                    </a>
                    <a href="{{ route('admin.dropi.catalog', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                        <i class="bi bi-download me-1"></i> Importar
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white">
                <div class="py-4">
                    <i class="bi bi-building fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="fw-bold mb-2">No se encontraron proveedores</h5>
                    <p class="text-muted small">Intenta buscar con otros términos o registra un nuevo proveedor.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center">
    {{ $suppliers->links('pagination::bootstrap-5') }}
</div>

<!-- Modal: Registrar Nuevo Proveedor -->
<div class="modal fade" id="newSupplierModal" tabindex="-1" aria-labelledby="newSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold" id="newSupplierModalLabel"><i class="bi bi-building-add text-primary me-2"></i>Registrar Bodega / Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-bold">Nombre del Proveedor / Bodega *</label>
                        <input type="text" name="name" id="name" class="form-control rounded-3" required placeholder="Ej: Bodega Central Cali Mayorista">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="city" class="form-label small fw-bold">Ciudad *</label>
                            <input type="text" name="city" id="city" class="form-control rounded-3" required placeholder="Ej: Medellín">
                        </div>
                        <div class="col-6">
                            <label for="department" class="form-label small fw-bold">Departamento</label>
                            <input type="text" name="department" id="department" class="form-control rounded-3" placeholder="Ej: Antioquia">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="phone" class="form-label small fw-bold">Teléfono / WhatsApp</label>
                            <input type="text" name="phone" id="phone" class="form-control rounded-3" placeholder="+57 300 123 4567">
                        </div>
                        <div class="col-6">
                            <label for="email" class="form-label small fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" id="email" class="form-control rounded-3" placeholder="proveedor@bodega.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="warehouse_address" class="form-label small fw-bold">Dirección de la Bodega</label>
                        <input type="text" name="warehouse_address" id="warehouse_address" class="form-control rounded-3" placeholder="Ej: Zona Industrial Itagüí, Bodega 14">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label small fw-bold">Descripción del Proveedor</label>
                        <textarea name="description" id="description" rows="2" class="form-control rounded-3" placeholder="Especialidad, tiempos de despacho, tipos de productos..."></textarea>
                    </div>

                    <div class="mb-2">
                        <label for="logo" class="form-label small fw-bold">URL del Logo (Opcional)</label>
                        <input type="url" name="logo" id="logo" class="form-control rounded-3" placeholder="https://...">
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Guardar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
