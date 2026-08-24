@extends('layouts.admin')

@section('title', $supplier->name)
@section('page_header', 'Catálogo de ' . $supplier->name)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}" class="text-muted text-decoration-none">Proveedores</a></li>
                <li class="breadcrumb-item active text-primary" aria-current="page">{{ $supplier->name }}</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">{{ $supplier->name }}</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.dropi.catalog', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-cloud-arrow-down me-1"></i> Abrir en Importador Dropi
        </a>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver a Proveedores
        </a>
    </div>
</div>

<!-- Supplier Header Card -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <div class="row align-items-center g-4">
        <div class="col-auto">
            <img src="{{ $supplier->logo }}" alt="{{ $supplier->name }}" class="rounded-4 object-fit-cover shadow-sm border" style="width: 80px; height: 80px;">
        </div>
        <div class="col">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-dark">{{ $supplier->name }}</h4>
                @if($supplier->is_verified)
                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1 small">
                        <i class="bi bi-patch-check-fill"></i> Verificado Dropi
                    </span>
                @endif
            </div>
            <p class="text-muted small mb-2">{{ $supplier->description }}</p>
            <div class="d-flex flex-wrap gap-3 small text-secondary">
                <span><i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $supplier->warehouse_address ?: ($supplier->city . ', ' . $supplier->department) }}</span>
                <span><i class="bi bi-telephone-fill text-success me-1"></i> {{ $supplier->phone ?: 'No registrado' }}</span>
                <span><i class="bi bi-envelope-fill text-primary me-1"></i> {{ $supplier->email ?: 'No registrado' }}</span>
                <span><i class="bi bi-star-fill text-warning me-1"></i> <strong>{{ number_format($supplier->rating, 1) }} / 5.0</strong></span>
            </div>
        </div>
    </div>
</div>

<!-- Supplier Products Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">Productos en Bodega ({{ $supplier->catalogProducts->count() }})</h5>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="bg-light small text-muted text-uppercase">
                <tr>
                    <th class="ps-4 py-3">Producto</th>
                    <th class="py-3">Categoría</th>
                    <th class="py-3">Costo Mayorista</th>
                    <th class="py-3">Precio Sugerido</th>
                    <th class="py-3">Ganancia Estimada</th>
                    <th class="py-3">Stock Bodega</th>
                    <th class="pe-4 py-3 text-end">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($supplier->catalogProducts as $sp)
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $sp->image }}" alt="{{ $sp->name }}" class="rounded-3 object-fit-cover shadow-sm" style="width: 50px; height: 50px;">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $sp->name }}</h6>
                                    <small class="text-muted font-monospace">SKU: {{ $sp->sku }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 small text-muted">{{ $sp->category_name }}</td>
                        <td class="py-3 fw-semibold text-dark">{{ format_cop($sp->wholesale_price) }}</td>
                        <td class="py-3 text-primary fw-bold">{{ format_cop($sp->suggested_price) }}</td>
                        <td class="py-3">
                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1">
                                +{{ format_cop($sp->potential_profit) }} ({{ $sp->margin_percent }}%)
                            </span>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark border">{{ $sp->stock }} disp.</span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            @if($sp->is_imported)
                                <span class="badge bg-success rounded-pill px-3 py-2">
                                    <i class="bi bi-check-circle-fill me-1"></i> Importado
                                </span>
                            @else
                                <form action="{{ route('admin.dropi.catalog.import', $sp->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                                        <i class="bi bi-download me-1"></i> Importar a Tienda
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No hay productos registrados para este proveedor.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
