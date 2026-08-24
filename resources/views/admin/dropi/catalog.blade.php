@extends('layouts.admin')

@section('title', 'Importador de Productos Dropi')
@section('page_header', 'Importador de Productos Dropi / Proveedores')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-cloud-arrow-down text-primary me-2"></i>Catálogo Mayorista Dropi (Colombia)</h4>
        <p class="text-muted small mb-0">Selecciona productos de bodegas mayoristas en Colombia, define tu margen de ganancia en COP e impórtalos a tu tienda en 1 clic.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#importAllCatalogModal">
            <i class="bi bi-cloud-arrow-down-fill"></i> Importar Todo el Catálogo
        </button>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-building me-1"></i> Ver Bodegas
        </a>
        <a href="{{ route('admin.dropi.orders') }}" class="btn btn-outline-primary rounded-pill px-3">
            <i class="bi bi-truck me-1"></i> Despachos Dropi
        </a>
    </div>
</div>

<!-- Modal Importar Todo Dropi -->
<div class="modal fade" id="importAllCatalogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-cloud-arrow-down-fill text-primary"></i> Importar Todo el Catálogo Dropi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.dropi.catalog.import_all') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <p class="text-muted small mb-3">
                        Se importarán todos los productos disponibles de los proveedores Dropi a tu tienda virtual. Puedes personalizar el margen de ganancia porcentual que se sumará al precio mayorista.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Margen de Ganancia Global (%)</label>
                        <div class="input-group">
                            <input type="number" name="markup_percent" class="form-control rounded-start-3" value="40" min="1" max="500" required>
                            <span class="input-group-text rounded-end-3 bg-light fw-bold">% sobre costo</span>
                        </div>
                        <div class="form-text small text-muted">Ejemplo: Con 40%, un producto de $50.000 COP se publicará a $70.000 COP.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-cloud-arrow-down-fill me-1"></i> Iniciar Importación Completa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
    <form action="{{ route('admin.dropi.catalog') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Buscar por producto o SKU...">
            </div>
        </div>

        <div class="col-md-3">
            <select name="supplier_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todas las Bodegas / Proveedores</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                        {{ $sup->name }} ({{ $sup->city }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="is_imported" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todos los Estados</option>
                <option value="0" {{ request('is_imported') === '0' ? 'selected' : '' }}>No Importados</option>
                <option value="1" {{ request('is_imported') === '1' ? 'selected' : '' }}>Ya Importados</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Más Recientes</option>
                <option value="profit_desc" {{ request('sort') === 'profit_desc' ? 'selected' : '' }}>Mayor Ganancia COP</option>
                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Menor Costo Mayorista</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Mayor Costo Mayorista</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100">Filtrar</button>
            @if(request()->hasAny(['q', 'supplier_id', 'is_imported', 'sort']))
                <a href="{{ route('admin.dropi.catalog') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Bulk Import Bar (Form wrapper) -->
<form action="{{ route('admin.dropi.catalog.bulk') }}" method="POST" id="bulkImportForm">
    @csrf
    <div class="card border-0 shadow-sm rounded-4 bg-primary-subtle border-primary border-opacity-25 p-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="form-check ms-2">
                <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                <label class="form-check-label fw-bold text-dark small" for="selectAllCheckbox">
                    Seleccionar Todos (<span id="selectedCount">0</span> seleccionados)
                </label>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted fw-semibold text-nowrap">Margen de Ganancia:</label>
                <div class="input-group input-group-sm" style="width: 130px;">
                    <input type="number" name="markup_percent" value="{{ $settings->default_markup_percent ?: 40 }}" class="form-control text-center" min="5" max="300">
                    <span class="input-group-text">%</span>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" id="bulkImportBtn" disabled>
                <i class="bi bi-download me-1"></i> Importar Seleccionados a Tienda
            </button>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row g-4 mb-4">
        @forelse($supplierProducts as $sp)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100 d-flex flex-column overflow-hidden position-relative hover-lift">
                    <!-- Checkbox overlay -->
                    <div class="position-absolute top-0 start-0 m-2 z-3">
                        <input type="checkbox" name="product_ids[]" value="{{ $sp->id }}" class="form-check-input p-2 product-checkbox shadow-sm" onchange="updateSelectedCount()" {{ $sp->is_imported ? 'disabled' : '' }}>
                    </div>

                    <!-- Image & Badges -->
                    <div class="position-relative bg-light text-center overflow-hidden" style="height: 180px;">
                        <img src="{{ $sp->image }}" alt="{{ $sp->name }}" class="w-100 h-100 object-fit-cover">
                        <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-75 rounded-pill small">
                            <i class="bi bi-building"></i> {{ $sp->supplier ? $sp->supplier->city : 'Dropi' }}
                        </span>
                        @if($sp->is_imported)
                            <span class="position-absolute top-0 end-0 m-2 badge bg-success rounded-pill px-2 py-1">
                                <i class="bi bi-check-circle-fill"></i> Importado
                            </span>
                        @endif
                    </div>

                    <!-- Body -->
                    <div class="card-body p-3 d-flex flex-column">
                        <small class="text-muted text-uppercase mb-1" style="font-size: 0.7rem;">{{ $sp->category_name }}</small>
                        <h6 class="fw-bold text-dark mb-2 text-truncate" title="{{ $sp->name }}">{{ $sp->name }}</h6>

                        <!-- Pricing Breakdown in COP -->
                        <div class="bg-light p-2 rounded-3 mb-3 small border">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Costo Proveedor:</span>
                                <strong class="text-dark">{{ format_cop($sp->wholesale_price) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Venta Sugerida:</span>
                                <strong class="text-primary">{{ format_cop($sp->suggested_price) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between pt-1 border-top text-success fw-bold">
                                <span>Tu Ganancia:</span>
                                <span>+{{ format_cop($sp->potential_profit) }} ({{ $sp->margin_percent }}%)</span>
                            </div>
                        </div>

                        <!-- Stock & Bodega Info -->
                        <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                            <span><i class="bi bi-box me-1"></i> Stock: <strong>{{ $sp->stock }}</strong></span>
                            <span>{{ $sp->supplier ? Str::limit($sp->supplier->name, 15) : 'Dropi Bodega' }}</span>
                        </div>

                        <!-- Import Action -->
                        <div class="mt-auto">
                            @if($sp->is_imported)
                                <div class="d-grid">
                                    <a href="{{ route('admin.products.index', ['q' => $sp->sku]) }}" class="btn btn-outline-success btn-sm rounded-pill disabled">
                                        <i class="bi bi-check-circle-fill me-1"></i> Ya en Tienda
                                    </a>
                                </div>
                            @else
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill flex-grow-1 fw-semibold" onclick="openImportModal({{ $sp->id }}, '{{ addslashes($sp->name) }}', {{ $sp->wholesale_price }}, {{ $sp->suggested_price }})">
                                        <i class="bi bi-download me-1"></i> Importar
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white">
                    <div class="py-4">
                        <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="fw-bold mb-2">No se encontraron productos en el catálogo de proveedores</h5>
                        <p class="text-muted small">Intenta ajustar los filtros de búsqueda o cambiar la bodega seleccionada.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</form>

<div class="d-flex justify-content-center">
    {{ $supplierProducts->links('pagination::bootstrap-5') }}
</div>

<!-- Modal: Importar Producto Individual con Margen Personalizado -->
<div class="modal fade" id="singleImportModal" tabindex="-1" aria-labelledby="singleImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold" id="singleImportModalLabel"><i class="bi bi-cloud-download text-primary me-2"></i>Importar a Tienda Virtual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="singleImportForm" method="POST">
                @csrf
                <div class="modal-body px-4 py-3">
                    <h6 id="modalProductName" class="fw-bold text-dark mb-3"></h6>

                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">Costo Proveedor / Bodega:</span>
                            <strong id="modalWholesalePrice" class="text-dark">$ 0</strong>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Precio Sugerido Dropi:</span>
                            <strong id="modalSuggestedPrice" class="text-primary">$ 0</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modalSalePrice" class="form-label small fw-bold">Precio de Venta al Público ($ COP) *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="500" name="sale_price" id="modalSalePrice" class="form-control fw-bold" required oninput="calculateModalProfit()">
                        </div>
                    </div>

                    <!-- Calculated Profit Card -->
                    <div class="alert alert-success d-flex justify-content-between align-items-center mb-3 rounded-3 p-3">
                        <div>
                            <span class="d-block small text-muted">Tu Ganancia Neta por Venta:</span>
                            <strong id="modalCalculatedProfit" class="fs-5 text-success">$ 0 COP</strong>
                        </div>
                        <span id="modalCalculatedMargin" class="badge bg-success px-2 py-1 fs-6">0%</span>
                    </div>

                    <div class="mb-2">
                        <label for="modalCategory" class="form-label small fw-bold">Categoría en Tienda</label>
                        <select name="category_id" id="modalCategory" class="form-select rounded-3">
                            <option value="">Crear o asignar categoría automáticamente</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="bi bi-download me-1"></i> Importar Producto Ahora
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentWholesale = 0;

    function openImportModal(id, name, wholesale, suggested) {
        currentWholesale = wholesale;
        document.getElementById('modalProductName').textContent = name;
        document.getElementById('modalWholesalePrice').textContent = '$ ' + Math.round(wholesale).toLocaleString('es-CO');
        document.getElementById('modalSuggestedPrice').textContent = '$ ' + Math.round(suggested).toLocaleString('es-CO');
        document.getElementById('modalSalePrice').value = suggested;
        document.getElementById('singleImportForm').action = `/admin/dropi/catalog/import/${id}`;
        calculateModalProfit();

        const modal = new bootstrap.Modal(document.getElementById('singleImportModal'));
        modal.show();
    }

    function calculateModalProfit() {
        const salePrice = parseFloat(document.getElementById('modalSalePrice').value) || 0;
        const profit = salePrice - currentWholesale;
        const margin = currentWholesale > 0 ? Math.round((profit / currentWholesale) * 100) : 0;

        document.getElementById('modalCalculatedProfit').textContent = '$ ' + Math.round(profit).toLocaleString('es-CO') + ' COP';
        document.getElementById('modalCalculatedMargin').textContent = margin + '% Margen';
    }

    function toggleSelectAll(master) {
        document.querySelectorAll('.product-checkbox:not(:disabled)').forEach(cb => {
            cb.checked = master.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.product-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = checked;
        document.getElementById('bulkImportBtn').disabled = checked === 0;
    }
</script>
@endpush
