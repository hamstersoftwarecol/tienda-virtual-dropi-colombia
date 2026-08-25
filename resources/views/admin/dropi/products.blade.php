@extends('layouts.admin')

@section('title', 'Catálogo & Importador Dropi')
@section('page_header', 'Catálogo de Productos Dropi Colombia')

@section('content')
<!-- Header Card with Stats & Bulk Actions -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold">
                    <i class="bi bi-box-arrow-in-down-fill me-1"></i> Catálogo Dropi Colombia
                </span>
                @if($dropiToken && $dropiToken->is_valid)
                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                        <i class="bi bi-patch-check-fill me-1"></i> Token Activo ({{ $dropiToken->store }})
                    </span>
                @endif
                <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                    {{ $importedCount }} de {{ count($dropiProducts) }} importados a tu tienda
                </span>
            </div>
            <h4 class="fw-bold mb-1 text-dark">Visualizar &amp; Importar Productos Dropi</h4>
            <p class="text-muted small mb-0">
                Selecciona cualquier producto para importarlo instantáneamente a tu tienda virtual con un solo clic.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <form action="{{ route('admin.dropi.products.import_all') }}" method="POST" onsubmit="return confirm('¿Importar todos los productos del catálogo de Dropi a tu tienda con sus precios sugeridos?');">
                @csrf
                <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm">
                    <i class="bi bi-cloud-arrow-down-fill me-1"></i> ⚡ Importar Todo el Catálogo (1-Clic)
                </button>
            </form>
            <a href="{{ route('admin.products.index') }}" class="btn btn-light rounded-pill px-3 border">
                <i class="bi bi-box-seam me-1"></i> Ver Mis Productos
            </a>
        </div>
    </div>
</div>

<!-- Filters & Search Bar -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
    <div class="row g-3 align-items-center justify-content-between">
        <!-- Search Input -->
        <div class="col-md-6">
            <form action="{{ route('admin.dropi.products.index') }}" method="GET" class="input-group">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control bg-light border-start-0 rounded-end-pill" 
                       placeholder="Buscar en el catálogo de Dropi..." value="{{ $search }}">
                @if($search)
                    <a href="{{ route('admin.dropi.products.index', ['category' => request('category')]) }}" class="btn btn-light border rounded-pill ms-2">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Category Filters -->
        <div class="col-md-6">
            <div class="d-flex flex-wrap gap-1 justify-content-md-end">
                <a href="{{ route('admin.dropi.products.index', ['search' => $search]) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ empty($selectedCategory) || $selectedCategory === 'all' ? 'btn-primary' : 'btn-light border' }}">
                    Todos
                </a>
                <a href="{{ route('admin.dropi.products.index', ['category' => 'Tecnología', 'search' => $search]) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $selectedCategory === 'Tecnología' ? 'btn-primary' : 'btn-light border' }}">
                    Tecnología
                </a>
                <a href="{{ route('admin.dropi.products.index', ['category' => 'Belleza & Cuidado Personal', 'search' => $search]) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $selectedCategory === 'Belleza & Cuidado Personal' ? 'btn-primary' : 'btn-light border' }}">
                    Belleza
                </a>
                <a href="{{ route('admin.dropi.products.index', ['category' => 'Hogar & Cocina', 'search' => $search]) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $selectedCategory === 'Hogar & Cocina' ? 'btn-primary' : 'btn-light border' }}">
                    Hogar
                </a>
                <a href="{{ route('admin.dropi.products.index', ['category' => 'Salud & Bienestar', 'search' => $search]) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $selectedCategory === 'Salud & Bienestar' ? 'btn-primary' : 'btn-light border' }}">
                    Salud
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Products Grid (Direct Visual Catalog) -->
<div class="row g-4 mb-4">
    @forelse($dropiProducts as $prod)
        @php
            $isImported = in_array($prod['id'], $importedDropiIds);
            $wholesale = (float) $prod['wholesale_price'];
            $suggested = (float) $prod['suggested_price'];
            $profit = max(0, $suggested - $wholesale);
            $profitPercent = $wholesale > 0 ? round(($profit / $wholesale) * 100) : 0;
        @endphp
        <div class="col-sm-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white d-flex flex-column transition-all hover-shadow {{ $isImported ? 'border-success border-2' : '' }}">
                <!-- Product Image & Badges -->
                <div class="position-relative bg-light text-center p-3" style="height: 200px;">
                    <img src="{{ $prod['image'] }}" alt="{{ $prod['name'] }}" class="h-100 w-100 object-fit-contain transition-transform">
                    
                    <span class="position-absolute top-0 start-0 m-2 badge bg-dark bg-opacity-75 rounded-pill small font-monospace">
                        {{ $prod['id'] }}
                    </span>

                    @if($isImported)
                        <span class="position-absolute top-0 end-0 m-2 badge bg-success text-white rounded-pill px-2 py-1 shadow-sm">
                            <i class="bi bi-check-circle-fill me-1"></i> En Tu Tienda
                        </span>
                    @else
                        <span class="position-absolute top-0 end-0 m-2 badge bg-primary-subtle text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 small">
                            {{ $prod['category'] }}
                        </span>
                    @endif
                </div>

                <!-- Product Content -->
                <div class="card-body p-3 d-flex flex-column flex-grow-1">
                    <h6 class="fw-bold text-dark mb-2 text-truncate-2" style="font-size: 0.95rem; min-height: 2.7rem;" title="{{ $prod['name'] }}">
                        {{ $prod['name'] }}
                    </h6>

                    <!-- Price & Profit Box -->
                    <div class="bg-light p-2 rounded-3 mb-3 small">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">Costo Dropi:</span>
                            <strong class="text-secondary">{{ format_cop($wholesale) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">Precio de Venta:</span>
                            <strong class="text-dark fs-6">{{ format_cop($suggested) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                            <span class="text-success fw-bold">Tu Ganancia:</span>
                            <span class="badge bg-success text-white rounded-pill px-2 py-1 font-monospace">
                                +{{ format_cop($profit) }} ({{ $profitPercent }}%)
                            </span>
                        </div>
                    </div>

                    <!-- Direct 1-Click Import Actions -->
                    <div class="mt-auto d-flex flex-column gap-2">
                        @if($isImported)
                            <div class="d-flex gap-2">
                                <span class="btn btn-outline-success w-100 rounded-pill btn-sm fw-bold disabled">
                                    <i class="bi bi-check2-circle me-1"></i> Ya Importado
                                </span>
                                <button type="button" class="btn btn-light border rounded-circle btn-sm" title="Ajustar precio o actualizar"
                                        onclick="openPriceCustomizer('{{ $prod['id'] }}', '{{ addslashes($prod['name']) }}', '{{ $wholesale }}', '{{ $suggested }}', '{{ $prod['image'] }}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        @else
                            <form action="{{ route('admin.dropi.products.import') }}" method="POST" class="w-100">
                                @csrf
                                <input type="hidden" name="dropi_id" value="{{ $prod['id'] }}">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm btn-sm py-2">
                                    <i class="bi bi-cloud-arrow-down-fill me-1"></i> Importar Producto
                                </button>
                            </form>
                            
                            <button type="button" class="btn btn-link btn-sm text-decoration-none text-muted p-0 text-center"
                                    onclick="openPriceCustomizer('{{ $prod['id'] }}', '{{ addslashes($prod['name']) }}', '{{ $wholesale }}', '{{ $suggested }}', '{{ $prod['image'] }}')">
                                <small><i class="bi bi-sliders me-1"></i>Personalizar precio antes de importar</small>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-box-seam fs-1 text-muted d-block mb-3"></i>
            <h5 class="fw-bold text-dark">No se encontraron productos con ese filtro</h5>
            <a href="{{ route('admin.dropi.products.index') }}" class="btn btn-primary rounded-pill px-4 mt-2">
                Ver todos los productos
            </a>
        </div>
    @endforelse
</div>

<!-- Modal Opcional: Personalizar Precio antes de importar -->
<div class="modal fade" id="priceCustomizerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-sliders text-primary me-2"></i>Ajustar Precio de Venta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.dropi.products.import') }}" method="POST">
                @csrf
                <input type="hidden" name="dropi_id" id="customDropiId">

                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3 bg-light p-3 rounded-3 border">
                        <img src="" id="customImgPreview" class="rounded-3 object-fit-contain bg-white border" style="width: 60px; height: 60px;">
                        <div>
                            <h6 class="fw-bold text-dark mb-0" id="customProdTitle"></h6>
                            <small class="text-muted font-monospace" id="customDropiIdBadge"></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Costo Mayorista Dropi:</label>
                        <div class="form-control bg-light font-monospace fw-bold text-secondary" id="customWholesaleDisplay">$ 0 COP</div>
                    </div>

                    <div class="mb-3">
                        <label for="customPriceInput" class="form-label small fw-bold text-dark">Tu Precio de Venta en Tienda (COP) *</label>
                        <input type="number" name="custom_price" id="customPriceInput" class="form-control font-monospace fw-bold text-primary border-primary fs-5" 
                               step="100" min="1000" oninput="calculateCustomMargin()" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Tu Ganancia Neta Calculada:</label>
                        <div class="form-control bg-success-subtle text-success font-monospace fw-bold border-success" id="customProfitDisplay">
                            + $ 0 COP (0%)
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="customCategorySelect" class="form-label small fw-bold">Asignar a Categoría</label>
                        <select name="category_id" id="customCategorySelect" class="form-select rounded-3">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3 bg-light">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Guardar e Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentWholesaleCost = 0;

function openPriceCustomizer(id, name, wholesale, suggested, image) {
    currentWholesaleCost = parseFloat(wholesale) || 0;
    const sugg = parseFloat(suggested) || (currentWholesaleCost * 1.5);

    document.getElementById('customDropiId').value = id;
    document.getElementById('customDropiIdBadge').textContent = 'Dropi ID #' + id;
    document.getElementById('customProdTitle').textContent = name;
    document.getElementById('customImgPreview').src = image;
    document.getElementById('customWholesaleDisplay').textContent = '$ ' + currentWholesaleCost.toLocaleString('es-CO') + ' COP';
    document.getElementById('customPriceInput').value = Math.round(sugg);

    calculateCustomMargin();

    const modal = new bootstrap.Modal(document.getElementById('priceCustomizerModal'));
    modal.show();
}

function calculateCustomMargin() {
    const sale = parseFloat(document.getElementById('customPriceInput').value) || 0;
    const profit = sale - currentWholesaleCost;
    const percent = currentWholesaleCost > 0 ? Math.round((profit / currentWholesaleCost) * 100) : 0;

    const display = document.getElementById('customProfitDisplay');
    if (profit >= 0) {
        display.className = 'form-control bg-success-subtle text-success font-monospace fw-bold border-success';
        display.textContent = '+ $ ' + profit.toLocaleString('es-CO') + ' COP (' + percent + '%)';
    } else {
        display.className = 'form-control bg-danger-subtle text-danger font-monospace fw-bold border-danger';
        display.textContent = '- $ ' + Math.abs(profit).toLocaleString('es-CO') + ' COP (' + percent + '%)';
    }
}
</script>
@endsection
