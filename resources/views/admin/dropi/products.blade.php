@extends('layouts.admin')

@section('title', 'Importador de Productos Dropi')
@section('page_header', 'Catálogo & Importador Dropi')

@section('content')
<!-- Header Banner -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold">
                    <i class="bi bi-box-arrow-in-down me-1"></i> API Dropi Colombia
                </span>
                @if($dropiToken && $dropiToken->is_valid)
                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                        <i class="bi bi-patch-check-fill me-1"></i> Token Conectado: {{ $dropiToken->store }}
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Token no validado
                    </span>
                @endif
            </div>
            <h4 class="fw-bold mb-1 text-dark">Explorador & Importador de Productos Dropi</h4>
            <p class="text-muted small mb-0">
                Visualiza los productos disponibles en Dropi e impórtalos directamente a tu tienda con margen y precio de venta personalizado en COP.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 fw-semibold btn-sm" data-bs-toggle="modal" data-bs-target="#manualImportModal">
                <i class="bi bi-plus-circle me-1"></i> Importar por ID / Datos
            </button>
            <a href="{{ route('admin.dropi.settings') }}" class="btn btn-light rounded-pill px-3 border btn-sm">
                <i class="bi bi-gear me-1"></i> Token Settings
            </a>
        </div>
    </div>
</div>

<!-- Search & Filters -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
    <form action="{{ route('admin.dropi.products.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control bg-light border-start-0 rounded-end-pill" 
                       placeholder="Buscar productos en Dropi por nombre, palabra clave o ID..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold flex-grow-1">
                <i class="bi bi-search me-1"></i> Buscar en Dropi
            </button>
            @if($search)
                <a href="{{ route('admin.dropi.products.index') }}" class="btn btn-light rounded-pill px-3 border">
                    Limpiar
                </a>
            @endif
        </div>
    </form>
</div>

@if(!$apiSuccess && empty($dropiProducts))
    <div class="alert alert-info border-0 shadow-sm rounded-4 p-4 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-circle p-3 fs-3">
                <i class="bi bi-cloud-arrow-down"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="fw-bold mb-1 text-primary">Importación Directa & Sincronización</h5>
                <p class="small text-muted mb-2">
                    {{ $apiMessage ?: 'Puedes importar cualquier producto de Dropi a tu tienda usando su ID o formulario directo con cálculo en tiempo real de ganancia.' }}
                </p>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#manualImportModal">
                    <i class="bi bi-plus-lg me-1"></i> Importar Producto Ahora
                </button>
            </div>
        </div>
    </div>
@endif

<!-- Products Grid -->
<div class="row g-4 mb-4">
    @forelse($dropiProducts as $item)
        @php
            $prodId = $item['id'] ?? ($item['product_id'] ?? Str::random(6));
            $name = $item['name'] ?? ($item['product_name'] ?? 'Producto Dropi');
            $wholesale = (float) ($item['price'] ?? ($item['wholesale_price'] ?? 40000));
            $suggested = (float) ($item['suggested_price'] ?? ($wholesale * 1.5));
            $stock = (int) ($item['stock'] ?? 25);
            $image = !empty($item['image']) ? $item['image'] : (!empty($item['images'][0]) ? $item['images'][0] : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800');
            $desc = $item['description'] ?? '';
            $isImported = in_array((string)$prodId, $importedDropiIds);
        @endphp
        <div class="col-sm-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white d-flex flex-column transition-all hover-shadow">
                <!-- Image & Badges -->
                <div class="position-relative bg-light text-center p-3" style="height: 180px;">
                    <img src="{{ $image }}" alt="{{ $name }}" class="h-100 w-100 object-fit-contain">
                    <span class="position-absolute top-0 start-0 m-2 badge bg-dark bg-opacity-75 rounded-pill small font-monospace">
                        ID #{{ $prodId }}
                    </span>
                    @if($isImported)
                        <span class="position-absolute top-0 end-0 m-2 badge bg-success rounded-pill small">
                            <i class="bi bi-check-circle-fill me-1"></i> Importado
                        </span>
                    @endif
                </div>

                <!-- Body -->
                <div class="card-body p-3 d-flex flex-column flex-grow-1">
                    <h6 class="fw-bold text-dark mb-2 text-truncate-2" style="font-size: 0.95rem; min-height: 2.8rem;" title="{{ $name }}">
                        {{ $name }}
                    </h6>

                    <div class="bg-light p-2 rounded-3 mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Costo Dropi:</span>
                            <strong class="text-dark">{{ format_cop($wholesale) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Precio Sugerido:</span>
                            <strong class="text-primary">{{ format_cop($suggested) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Stock:</span>
                            <span class="badge {{ $stock > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                {{ $stock }} unid.
                            </span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-auto">
                        <button type="button" class="btn {{ $isImported ? 'btn-outline-success' : 'btn-primary' }} w-100 rounded-pill fw-bold btn-sm py-2"
                                onclick="openImportModal('{{ $prodId }}', '{{ addslashes($name) }}', '{{ $wholesale }}', '{{ $suggested }}', '{{ $stock }}', '{{ $image }}', '{{ addslashes(strip_tags($desc)) }}')">
                            <i class="bi {{ $isImported ? 'bi-arrow-repeat' : 'bi-download' }} me-1"></i>
                            {{ $isImported ? 'Actualizar en Tienda' : 'Importar a mi Tienda' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @empty
        @if($apiSuccess)
            <div class="col-12 text-center py-5">
                <i class="bi bi-box-seam fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-bold text-dark">No se encontraron productos en Dropi con ese criterio</h5>
                <p class="text-muted small">Intenta buscar con otra palabra clave o utiliza la importación manual.</p>
            </div>
        @endif
    @endforelse
</div>

<!-- Modal: Importar Producto Dropi con Precio & Categoría Personalizada -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-box-arrow-in-down text-primary me-2"></i>Configurar e Importar Producto a Mi Tienda
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.dropi.products.import') }}" method="POST" id="importForm">
                @csrf
                <input type="hidden" name="product" id="modalDropiId">
                <input type="hidden" name="wholesale_price" id="modalWholesale">
                <input type="hidden" name="sob_images[]" id="modalImage">
                <input type="hidden" name="store" value="{{ $dropiToken->store ?? 'Tienda 1' }}">

                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4 text-center">
                            <div class="bg-light p-2 rounded-3 border mb-2" style="height: 160px;">
                                <img src="" id="modalImgPreview" class="h-100 w-100 object-fit-contain" alt="Vista previa">
                            </div>
                            <span class="badge bg-dark font-monospace" id="modalDropiIdBadge">ID #</span>
                        </div>

                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="modalName" class="form-label small fw-bold">Nombre del Producto en la Tienda *</label>
                                <input type="text" name="product_name" id="modalName" class="form-control rounded-3" required>
                            </div>

                            <div class="mb-3">
                                <label for="modalCategory" class="form-label small fw-bold">Categoría en la Tienda *</label>
                                <select name="category_id" id="modalCategory" class="form-select rounded-3" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Live Margin Calculator -->
                    <div class="bg-light p-3 rounded-4 border mb-3">
                        <h6 class="fw-bold text-dark small mb-3 border-bottom pb-2">
                            <i class="bi bi-calculator text-success me-1"></i>Calculadora de Ganancia y Precio de Venta (COP)
                        </h6>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label small text-muted fw-bold">Costo Mayorista Dropi:</label>
                                <div class="form-control bg-white font-monospace fw-bold text-secondary" id="modalWholesaleDisplay">$ 0 COP</div>
                            </div>
                            <div class="col-md-4">
                                <label for="modalSalePrice" class="form-label small fw-bold text-dark">Precio de Venta en Tienda (COP) *</label>
                                <input type="number" name="product_price" id="modalSalePrice" class="form-control font-monospace fw-bold text-primary border-primary" 
                                       step="100" min="1000" oninput="calculateMargin()" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted fw-bold">Tu Ganancia Neta:</label>
                                <div class="form-control bg-success-subtle text-success font-monospace fw-bold border-success" id="modalProfitDisplay">
                                    + $ 0 COP (0%)
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="modalStock" class="form-label small fw-bold">Stock a Asignar *</label>
                            <input type="number" name="sob_stock" id="modalStock" class="form-control rounded-3" min="1" max="9999" value="20" required>
                        </div>
                        <div class="col-md-6">
                            <label for="modalComparePrice" class="form-label small fw-bold">Precio Antes / Tachado (Opcional)</label>
                            <input type="number" name="compare_price" id="modalComparePrice" class="form-control rounded-3" placeholder="Ej: 120000">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="modalDesc" class="form-label small fw-bold">Descripción del Producto</label>
                        <textarea name="product_description" id="modalDesc" rows="3" class="form-control rounded-3 small"></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3 bg-light">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-download me-1"></i> Confirmar e Importar a Mi Tienda
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Importación Manual / Directa -->
<div class="modal fade" id="manualImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-plus-circle text-primary me-2"></i>Importación Rápida de Producto Dropi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.dropi.products.import') }}" method="POST">
                @csrf
                <input type="hidden" name="store" value="{{ $dropiToken->store ?? 'Tienda 1' }}">
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label for="manDropiId" class="form-label small fw-bold">ID del Producto en Dropi *</label>
                        <input type="text" name="product" id="manDropiId" class="form-control rounded-3 font-monospace" placeholder="Ej: 145920" required>
                    </div>
                    <div class="mb-3">
                        <label for="manName" class="form-label small fw-bold">Nombre del Producto *</label>
                        <input type="text" name="product_name" id="manName" class="form-control rounded-3" placeholder="Ej: Smartwatch Deportivo T900 Ultra" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="manPrice" class="form-label small fw-bold">Precio de Venta (COP) *</label>
                            <input type="number" name="product_price" id="manPrice" class="form-control rounded-3 font-monospace" placeholder="89000" required>
                        </div>
                        <div class="col-6">
                            <label for="manWholesale" class="form-label small fw-bold">Costo Mayorista Dropi</label>
                            <input type="number" name="wholesale_price" id="manWholesale" class="form-control rounded-3 font-monospace" placeholder="45000">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="manCategory" class="form-label small fw-bold">Categoría *</label>
                        <select name="category_id" id="manCategory" class="form-select rounded-3" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label for="manImage" class="form-label small fw-bold">URL de la Imagen Principal</label>
                        <input type="url" name="image" id="manImage" class="form-control rounded-3" placeholder="https://...">
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-cloud-arrow-down me-1"></i> Importar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentWholesale = 0;

function openImportModal(id, name, wholesale, suggested, stock, image, desc) {
    currentWholesale = parseFloat(wholesale) || 0;
    const sugg = parseFloat(suggested) || (currentWholesale * 1.5);

    document.getElementById('modalDropiId').value = id;
    document.getElementById('modalDropiIdBadge').textContent = 'Dropi ID #' + id;
    document.getElementById('modalName').value = name;
    document.getElementById('modalWholesale').value = currentWholesale;
    document.getElementById('modalWholesaleDisplay').textContent = '$ ' + currentWholesale.toLocaleString('es-CO') + ' COP';
    document.getElementById('modalSalePrice').value = Math.round(sugg);
    document.getElementById('modalComparePrice').value = Math.round(sugg * 1.25);
    document.getElementById('modalStock').value = stock || 20;
    document.getElementById('modalImage').value = image;
    document.getElementById('modalImgPreview').src = image;
    document.getElementById('modalDesc').value = desc;

    calculateMargin();

    const modal = new bootstrap.Modal(document.getElementById('importModal'));
    modal.show();
}

function calculateMargin() {
    const salePrice = parseFloat(document.getElementById('modalSalePrice').value) || 0;
    const profit = salePrice - currentWholesale;
    const percent = currentWholesale > 0 ? Math.round((profit / currentWholesale) * 100) : 0;

    const display = document.getElementById('modalProfitDisplay');
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
