@extends('layouts.admin')

@section('title', 'Catálogo Dropi - Importar Productos')
@section('page_header', 'Catálogo Dropi (Colombia)')

@section('content')
<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-box-seam text-primary me-2"></i>Catálogo de Productos Dropi</h4>
        <p class="text-muted small mb-0">Explora todos los productos de Dropi disponibles para importar a tu tienda virtual con margen de ganancia en COP.</p>
    </div>
</div>

<!-- Filters & Search Bar -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
    <form action="{{ route('admin.dropi.catalog') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Buscar por nombre de producto o SKU...">
            </div>
        </div>

        <div class="col-md-3">
            <select name="is_imported" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                <option value="">Todos los Estados</option>
                <option value="0" {{ request('is_imported') === '0' ? 'selected' : '' }}>No Importados (Disponibles)</option>
                <option value="1" {{ request('is_imported') === '1' ? 'selected' : '' }}>Ya Importados en Tienda</option>
            </select>
        </div>

        <div class="col-md-3">
            <select name="sort" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Más Recientes</option>
                <option value="profit_desc" {{ request('sort') === 'profit_desc' ? 'selected' : '' }}>Mayor Ganancia COP</option>
                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Menor Costo Dropi</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Mayor Costo Dropi</option>
            </select>
        </div>

        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold">Filtrar</button>
            @if(request()->hasAny(['q', 'is_imported', 'sort']))
                <a href="{{ route('admin.dropi.catalog') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Products Grid -->
<div class="row g-4 mb-4">
    @forelse($supplierProducts as $sp)
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100 d-flex flex-column overflow-hidden position-relative hover-lift" style="transition: transform 0.2s, box-shadow 0.2s;">
                <!-- Image & Status Badge -->
                <div class="position-relative bg-light text-center overflow-hidden" style="height: 190px;">
                    <img src="{{ $sp->image }}" alt="{{ $sp->name }}" class="w-100 h-100 object-fit-cover">
                    @if($sp->is_imported)
                        <span class="position-absolute top-0 end-0 m-2 badge bg-success shadow-sm rounded-pill px-2 py-1">
                            <i class="bi bi-check-circle-fill"></i> En Tu Tienda
                        </span>
                    @else
                        <span class="position-absolute top-0 end-0 m-2 badge bg-primary shadow-sm rounded-pill px-2 py-1">
                            <i class="bi bi-box-arrow-in-down"></i> Listo para Importar
                        </span>
                    @endif
                </div>

                <!-- Body -->
                <div class="card-body p-3 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">{{ $sp->category_name }}</small>
                        <span class="badge bg-light text-dark border small fw-normal"><i class="bi bi-box-seam text-primary me-1"></i>Stock {{ $sp->stock }}</span>
                    </div>

                    <h6 class="fw-bold text-dark mb-1 text-truncate" title="{{ $sp->name }}">{{ $sp->name }}</h6>

                    @if($sp->supplier || !empty($sp->category_name))
                        <div class="mb-2 small d-flex align-items-center gap-1">
                            <span class="text-muted" style="font-size: 0.75rem;">Provider:</span>
                            <span class="text-primary fw-semibold text-truncate" style="font-size: 0.75rem;">
                                {{ $sp->supplier ? $sp->supplier->name : 'PROVEEDOR DROPI' }}
                            </span>
                            @if($sp->supplier && $sp->supplier->is_verified)
                                <span class="badge bg-warning text-dark px-1 py-0 rounded-pill ms-1" style="font-size: 0.65rem;">✓ Verificado</span>
                            @endif
                        </div>
                    @endif

                    <!-- Pricing Breakdown in COP -->
                    <div class="bg-light p-2 rounded-3 mb-3 small border">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Precio Proveedor:</span>
                            <strong class="text-dark">{{ format_cop($sp->wholesale_price) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Precio Sugerido:</span>
                            <strong class="text-primary">{{ format_cop($sp->suggested_price) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between pt-1 border-top text-success fw-bold">
                            <span>Tu Ganancia:</span>
                            <span>+{{ format_cop($sp->potential_profit) }} ({{ $sp->margin_percent }}%)</span>
                        </div>
                    </div>

                    <!-- Import Action Buttons -->
                    <div class="mt-auto">
                        @if($sp->is_imported)
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('admin.products.index', ['q' => $sp->sku]) }}" class="btn btn-outline-success btn-sm rounded-pill fw-semibold w-100">
                                    <i class="bi bi-check-circle-fill me-1"></i> Ver en Tienda
                                </a>
                                <form action="{{ route('admin.dropi.catalog.sync_product', $sp->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-light btn-sm rounded-pill w-100 text-muted border small" title="Sincronizar precio y stock actual con Dropi">
                                        <i class="bi bi-arrow-repeat text-primary me-1"></i> Actualizar Stock / Costo
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-2">
                                <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold import-single-btn shadow-sm"
                                        data-id="{{ $sp->id }}"
                                        data-name="{{ $sp->name }}"
                                        data-wholesale="{{ (float) $sp->wholesale_price }}"
                                        data-suggested="{{ (float) $sp->suggested_price }}"
                                        data-image="{{ $sp->image }}"
                                        data-sku="{{ $sp->sku }}"
                                        data-stock="{{ $sp->stock }}"
                                        data-category="{{ $sp->category_name }}"
                                        data-short-desc="{{ $sp->short_description }}"
                                        data-description="{{ $sp->description }}">
                                    <i class="bi bi-download me-1"></i> Importar a Mi Tienda
                                </button>
                                <form action="{{ route('admin.dropi.catalog.import', $sp->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill w-100 small" title="Importar con margen sugerido">
                                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Importación Rápida
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white">
                <div class="py-4 px-3" style="max-width: 550px; margin: 0 auto;">
                    <div class="stat-icon bg-primary-subtle text-primary mx-auto mb-3" style="width: 64px; height: 64px; font-size: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h5 class="fw-bold mb-2 text-dark">Catálogo Dropi Conectado</h5>
                    <p class="text-muted small mb-0">
                        Los productos de la API de Dropi se sincronizan automáticamente con las credenciales de tu Centro de Integraciones.
                    </p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center">
    {{ $supplierProducts->links('pagination::bootstrap-5') }}
</div>

<!-- Modal: Importar Producto con Datos Editables a Tienda -->
<div class="modal fade" id="singleImportModal" tabindex="-1" aria-labelledby="singleImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark" id="singleImportModalLabel">
                    <i class="bi bi-cloud-download text-primary me-2"></i>Importar Producto a Mi Tienda
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="singleImportForm" method="POST">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-3 text-center">
                            <div class="border rounded-4 p-2 bg-light mb-2" style="height: 140px;">
                                <img id="modalProductImgPreview" src="" alt="Vista previa" class="w-100 h-100 object-fit-cover rounded-3">
                            </div>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Foto Dropi</small>
                        </div>

                        <div class="col-md-9">
                            <div class="mb-3">
                                <label for="modalProductNameInput" class="form-label small fw-bold">Nombre del Producto en Tu Tienda *</label>
                                <input type="text" name="name" id="modalProductNameInput" class="form-control rounded-3" required>
                            </div>

                            <div class="mb-3">
                                <label for="modalProductImageInput" class="form-label small fw-bold">URL de la Imagen Principal</label>
                                <input type="url" name="image" id="modalProductImageInput" class="form-control rounded-3" oninput="document.getElementById('modalProductImgPreview').src = this.value">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Costo Dropi ($ COP)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" name="wholesale_price" id="modalWholesaleInput" class="form-control bg-light" readonly>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="modalSalePrice" class="form-label small fw-bold">Precio de Venta en Tienda ($ COP) *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="500" name="sale_price" id="modalSalePrice" class="form-control fw-bold text-primary" required oninput="calculateModalProfit()">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="modalProductStock" class="form-label small fw-bold">Cantidad / Stock</label>
                            <input type="number" name="stock" id="modalProductStock" class="form-control rounded-3" min="0" value="25">
                        </div>

                        <div class="col-12">
                            <div class="alert alert-success d-flex justify-content-between align-items-center rounded-3 p-3 mb-0">
                                <div>
                                    <span class="d-block small text-muted">Tu Ganancia Neta por Venta:</span>
                                    <strong id="modalCalculatedProfit" class="fs-5 text-success">$ 0 COP</strong>
                                </div>
                                <span id="modalCalculatedMargin" class="badge bg-success px-3 py-2 fs-6">0% Margen</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="modalCategory" class="form-label small fw-bold">Categoría en Tienda</label>
                            <select name="category_id" id="modalCategory" class="form-select rounded-3">
                                <option value="">Crear o asignar automáticamente</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="modalProductSku" class="form-label small fw-bold">SKU / ID</label>
                            <input type="text" name="sku" id="modalProductSku" class="form-control rounded-3 font-monospace">
                        </div>

                        <div class="col-12">
                            <label for="modalShortDesc" class="form-label small fw-bold">Descripción Corta</label>
                            <textarea name="short_description" id="modalShortDesc" rows="2" class="form-control rounded-3"></textarea>
                        </div>

                        <div class="col-12">
                            <label for="modalFullDesc" class="form-label small fw-bold">Descripción Completa</label>
                            <textarea name="description" id="modalFullDesc" rows="3" class="form-control rounded-3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-cloud-arrow-down-fill me-1"></i> Publicar en Mi Tienda
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentWholesale = 0;

    document.querySelectorAll('.import-single-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const wholesale = parseFloat(this.getAttribute('data-wholesale')) || 0;
            const suggested = parseFloat(this.getAttribute('data-suggested')) || 0;
            const image = this.getAttribute('data-image');
            const sku = this.getAttribute('data-sku');
            const stock = this.getAttribute('data-stock');
            const category = this.getAttribute('data-category');
            const shortDesc = this.getAttribute('data-short-desc');
            const fullDesc = this.getAttribute('data-description');

            currentWholesale = wholesale;

            // Update modal form action
            document.getElementById('singleImportForm').action = `{{ url('/admin/dropi/catalog/import') }}/${id}`;
            
            // Populate fields
            document.getElementById('modalProductNameInput').value = name;
            document.getElementById('modalProductImageInput').value = image;
            document.getElementById('modalProductImgPreview').src = image;
            document.getElementById('modalWholesaleInput').value = wholesale;
            document.getElementById('modalSalePrice').value = suggested > 0 ? suggested : Math.round(wholesale * 1.4);
            document.getElementById('modalProductStock').value = stock || 25;
            document.getElementById('modalProductSku').value = sku;
            document.getElementById('modalShortDesc').value = shortDesc || '';
            document.getElementById('modalFullDesc').value = fullDesc || '';

            calculateModalProfit();

            const modal = new bootstrap.Modal(document.getElementById('singleImportModal'));
            modal.show();
        });
    });

    function calculateModalProfit() {
        const salePrice = parseFloat(document.getElementById('modalSalePrice').value) || 0;
        const profit = Math.max(0, salePrice - currentWholesale);
        const marginPercent = currentWholesale > 0 ? Math.round((profit / currentWholesale) * 100) : 0;

        document.getElementById('modalCalculatedProfit').textContent = '$ ' + new Intl.NumberFormat('es-CO').format(profit) + ' COP';
        document.getElementById('modalCalculatedMargin').textContent = marginPercent + '% Margen';
    }
</script>
@endpush
@endsection
