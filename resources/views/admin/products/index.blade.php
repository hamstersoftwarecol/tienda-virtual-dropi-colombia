@extends('layouts.admin')

@section('title', 'Gestión de Productos')
@section('page_header', 'Catálogo de Productos (COP)')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Productos Registrados</h4>
        <p class="text-muted small mb-0">Administra precios en pesos colombianos, existencias, imágenes y estado.</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary rounded-pill px-3 shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Crear Producto
    </a>
</div>

<!-- Filters & Search Bar -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
    <form action="{{ route('admin.products.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Buscar por nombre o SKU...">
            </div>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todas las Categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="stock_status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todos los Estados de Stock</option>
                <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Stock Bajo (<= 5)</option>
                <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Agotados (0)</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100">Filtrar</button>
            @if(request()->hasAny(['q', 'category', 'stock_status']))
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="bg-light small text-muted text-uppercase">
                <tr>
                    <th class="ps-4 py-3">Producto</th>
                    <th class="py-3">Categoría</th>
                    <th class="py-3">Precio (COP)</th>
                    <th class="py-3">Stock</th>
                    <th class="py-3">Estado</th>
                    <th class="pe-4 py-3 text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="rounded-3 object-fit-cover shadow-sm" style="width: 50px; height: 50px;">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $product->name }}</h6>
                                    <small class="text-muted font-monospace">SKU: {{ $product->sku }}</small>
                                    @if($product->is_featured)
                                        <span class="badge bg-warning-subtle text-warning border border-warning ms-1" style="font-size: 0.65rem;">Destacado</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3 small">
                            {{ $product->category ? $product->category->name : 'Sin Categoría' }}
                        </td>
                        <td class="py-3">
                            <strong class="text-dark">{{ format_cop($product->price) }}</strong>
                            @if($product->compare_price)
                                <small class="text-muted text-decoration-line-through d-block" style="font-size: 0.75rem;">{{ format_cop($product->compare_price) }}</small>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($product->stock <= 0)
                                <span class="badge bg-danger rounded-pill px-2 py-1">Agotado (0)</span>
                            @elseif($product->stock <= 5)
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Bajo ({{ $product->stock }})</span>
                            @else
                                <span class="badge bg-success rounded-pill px-2 py-1">{{ $product->stock }} en stock</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($product->is_active)
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1">Activo</span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-2 py-1">Inactivo</span>
                            @endif
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('shop.show', $product->slug) }}" target="_blank" class="btn btn-outline-secondary" title="Ver en tienda">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No se encontraron productos con los criterios seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center">
    {{ $products->links('pagination::bootstrap-5') }}
</div>
@endsection
