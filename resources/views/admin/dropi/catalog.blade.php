@extends('layouts.admin')

@section('title', 'Importador Individual Dropi')
@section('page_header', 'Importador de Productos Dropi / Proveedores')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-cloud-arrow-down text-primary me-2"></i>Importador Individual Dropi (Colombia)</h4>
        <p class="text-muted small mb-0">Importa tus productos reales de Dropi: nombre, imágenes, costo mayorista, precio de venta en COP, cantidad, descripción y categoría.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#customDropiProductModal">
            <i class="bi bi-plus-circle-fill"></i> + Importar Producto Dropi (Individual)
        </button>
        <form action="{{ route('admin.dropi.catalog.sync_api') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary rounded-pill px-3 shadow-sm d-flex align-items-center gap-2" title="Sincronizar productos disponibles desde la API de Dropi">
                <i class="bi bi-arrow-repeat"></i> Sincronizar desde Dropi API
            </button>
        </form>
    </div>
</div>

@if(!empty($apiError))
    <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-start gap-3">
                <i class="bi bi-info-circle-fill text-warning fs-3 mt-1"></i>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Estado de Conexión Dropi API:</h6>
                    <p class="small text-muted mb-0">
                        {{ $apiError }}
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#updateTokenModal">
                    <i class="bi bi-key-fill me-1"></i> Pegar Token Dropi
                </button>
                <a href="{{ route('admin.integrations.index') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 text-nowrap">
                    Ver Integraciones
                </a>
            </div>
        </div>
    </div>
@endif

<!-- Modal Actualizar Token Dropi Rápido -->
<div class="modal fade" id="updateTokenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-key-fill text-warning"></i> Actualizar Token de Dropi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.integrations.dropi.settings') }}" method="POST">
                @csrf
                <input type="hidden" name="api_url" value="https://api.dropi.co/api/products/supplier/v1?user_id=441247">
                <input type="hidden" name="default_carrier" value="Coordinadora">
                <input type="hidden" name="default_markup_percent" value="40">
                <input type="hidden" name="auto_sync_orders" value="1">
                <div class="modal-body px-4 py-3">
                    <p class="small text-muted mb-3">
                        Pega el Token de sesión o API de tu cuenta Dropi (desde <a href="https://app.dropi.co" target="_blank" class="fw-bold text-decoration-none">app.dropi.co</a>) para sincronizar los productos de tu catálogo automáticamente.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Token de Autenticación Dropi *</label>
                        <textarea name="auth_token" rows="4" class="form-control font-monospace small rounded-3" placeholder="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-save me-1"></i> Guardar y Sincronizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Importar Producto Individual por API / Datos Reales -->
<div class="modal fade" id="customDropiProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-box-seam-fill text-primary"></i> Importar Producto Real Dropi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.dropi.catalog.import_custom') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <!-- Nombre del Producto -->
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">Nombre del Producto *</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="Ej: Trípode Profesional con Anillo LED 12 Pulgadas" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">SKU / ID Dropi</label>
                            <input type="text" name="sku" class="form-control rounded-3 font-monospace" placeholder="DRP-12345">
                        </div>

                        <!-- Imágenes del Producto -->
                        <div class="col-md-7">
                            <label class="form-label fw-bold small">URL de la Imagen Principal *</label>
                            <input type="url" name="image" id="customProductImageInput" class="form-control rounded-3" placeholder="https://..." oninput="document.getElementById('customImgPreview').src = this.value" required>
                            <small class="text-muted" style="font-size: 0.75rem;">Pega el enlace directo de la imagen del producto.</small>
                        </div>
                        <div class="col-md-5 text-center">
                            <div class="border rounded-3 p-2 bg-light d-flex align-items-center justify-content-center" style="height: 100px;">
                                <img id="customImgPreview" src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=300" alt="Vista previa" class="h-100 object-fit-contain rounded">
                            </div>
                        </div>

                        <!-- Categoría -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Categoría en Tienda</label>
                            <select name="category_id" class="form-select rounded-3">
                                <option value="">Seleccionar existente...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">O Nueva Categoría</label>
                            <input type="text" name="category_name" class="form-control rounded-3" placeholder="Ej: Tecnología, Belleza, Hogar">
                        </div>

                        <!-- Precios de Descripción y Ganancia -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Costo Mayorista Dropi ($ COP) *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="500" name="wholesale_price" id="customWholesalePrice" class="form-control" value="45000" required oninput="calcCustomProfit()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Precio de Venta al Público ($ COP) *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="500" name="sale_price" id="customSalePrice" class="form-control fw-bold text-primary" value="79000" required oninput="calcCustomProfit()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Cantidad en Stock *</label>
                            <input type="number" name="stock" class="form-control rounded-3" value="30" min="0" required>
                        </div>

                        <!-- Profit card -->
                        <div class="col-12">
                            <div class="alert alert-success d-flex justify-content-between align-items-center rounded-3 p-3 mb-0">
                                <div>
                                    <span class="small text-muted d-block">Tu Ganancia Neta por Venta:</span>
                                    <strong id="customCalculatedProfit" class="text-success fs-5">$ 34.000 COP</strong>
                                </div>
                                <span id="customCalculatedMargin" class="badge bg-success fs-6 px-3 py-2">76% Margen</span>
                            </div>
                        </div>

                        <!-- Descripciones -->
                        <div class="col-12">
                            <label class="form-label fw-bold small">Descripción Corta</label>
                            <textarea name="short_description" rows="2" class="form-control rounded-3" placeholder="Resumen clave del producto que verá el comprador"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Descripción Completa</label>
                            <textarea name="description" rows="4" class="form-control rounded-3" placeholder="Detalles técnicos, materiales, contenido de la caja y garantía"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-cloud-check-fill me-1"></i> Publicar en Mi Tienda
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
    <form action="{{ route('admin.dropi.catalog') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Buscar por nombre o SKU...">
            </div>
        </div>

        <div class="col-md-3">
            <select name="is_imported" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todos los Estados</option>
                <option value="0" {{ request('is_imported') === '0' ? 'selected' : '' }}>No Importados</option>
                <option value="1" {{ request('is_imported') === '1' ? 'selected' : '' }}>Ya Importados</option>
            </select>
        </div>

        <div class="col-md-3">
            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Más Recientes</option>
                <option value="profit_desc" {{ request('sort') === 'profit_desc' ? 'selected' : '' }}>Mayor Ganancia COP</option>
                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Menor Costo Mayorista</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Mayor Costo Mayorista</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100">Filtrar</button>
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
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100 d-flex flex-column overflow-hidden position-relative hover-lift">
                <!-- Image & Badges -->
                <div class="position-relative bg-light text-center overflow-hidden" style="height: 180px;">
                    <img src="{{ $sp->image }}" alt="{{ $sp->name }}" class="w-100 h-100 object-fit-cover">
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
                            <span class="text-muted">Costo Dropi:</span>
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

                    <!-- Stock Info -->
                    <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                        <span><i class="bi bi-box me-1"></i> Stock: <strong>{{ $sp->stock }}</strong></span>
                        <span>{{ $sp->sku }}</span>
                    </div>

                    <!-- Import Action Button -->
                    <div class="mt-auto">
                        @if($sp->is_imported)
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('admin.products.index', ['q' => $sp->sku]) }}" class="btn btn-outline-success btn-sm rounded-pill fw-semibold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Ver en Tienda
                                </a>
                                <form action="{{ route('admin.dropi.catalog.sync_product', $sp->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-light btn-sm rounded-pill w-100 text-muted border small" title="Sincronizar precio y stock actual con Dropi">
                                        <i class="bi bi-arrow-repeat text-primary me-1"></i> Sincronizar Stock / Precio
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
                                    <i class="bi bi-gear-fill me-1"></i> Personalizar e Importar
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
                <div class="py-4 px-3" style="max-width: 600px; margin: 0 auto;">
                    <div class="stat-icon bg-primary-subtle text-primary mx-auto mb-3" style="width: 64px; height: 64px; font-size: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-cloud-arrow-down-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2 text-dark">Catálogo Dropi Conectado</h5>
                    <p class="text-muted small mb-4">
                        Visualiza e importa tus productos directamente desde Dropi. Puedes sincronizar todos los productos de tu catálogo mayorista o agregar productos individuales con nombre, imágenes, costos en COP, cantidad, descripción y categoría.
                    </p>
                    
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#customDropiProductModal">
                            <i class="bi bi-plus-circle-fill me-1"></i> + Importar Producto Real Dropi
                        </button>
                        <form action="{{ route('admin.dropi.catalog.sync_api') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary rounded-pill px-3 shadow-sm fw-semibold">
                                <i class="bi bi-arrow-repeat me-1"></i> Sincronizar desde API
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-warning rounded-pill px-3 fw-semibold text-dark" data-bs-toggle="modal" data-bs-target="#updateTokenModal">
                            <i class="bi bi-key-fill text-warning me-1"></i> Pegar Token Dropi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center">
    {{ $supplierProducts->links('pagination::bootstrap-5') }}
</div>

<!-- Modal: Importar Producto Individual Completo con Datos Editables -->
<div class="modal fade" id="singleImportModal" tabindex="-1" aria-labelledby="singleImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark" id="singleImportModalLabel">
                    <i class="bi bi-cloud-download text-primary me-2"></i>Personalizar e Importar a Mi Tienda
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
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Vista previa</small>
                        </div>

                        <div class="col-md-9">
                            <div class="mb-3">
                                <label for="modalProductNameInput" class="form-label small fw-bold">Nombre del Producto en Tu Tienda *</label>
                                <input type="text" name="name" id="modalProductNameInput" class="form-control rounded-3" required>
                            </div>

                            <div class="mb-3">
                                <label for="modalProductImageInput" class="form-label small fw-bold">URL de la Imagen Principal *</label>
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
                            <label for="modalSalePrice" class="form-label small fw-bold">Precio de Venta ($ COP) *</label>
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
                            <label for="modalCategory" class="form-label small fw-bold">Categoría</label>
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
                        <i class="bi bi-download me-1"></i> Publicar en Tienda
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

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.import-single-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const wholesale = parseFloat(this.dataset.wholesale) || 0;
                const suggested = parseFloat(this.dataset.suggested) || 0;
                const image = this.dataset.image;
                const sku = this.dataset.sku;
                const stock = this.dataset.stock;
                const shortDesc = this.dataset.shortDesc;
                const description = this.dataset.description;

                currentWholesale = wholesale;
                document.getElementById('modalProductNameInput').value = name;
                document.getElementById('modalProductImgPreview').src = image;
                document.getElementById('modalProductImageInput').value = image;
                document.getElementById('modalWholesaleInput').value = wholesale;
                document.getElementById('modalSalePrice').value = suggested;
                document.getElementById('modalProductStock').value = stock;
                document.getElementById('modalProductSku').value = sku;
                document.getElementById('modalShortDesc').value = shortDesc || '';
                document.getElementById('modalFullDesc').value = description || '';

                document.getElementById('singleImportForm').action = `/admin/dropi/catalog/import/${id}`;
                calculateModalProfit();

                const modal = new bootstrap.Modal(document.getElementById('singleImportModal'));
                modal.show();
            });
        });
    });

    function calculateModalProfit() {
        const salePrice = parseFloat(document.getElementById('modalSalePrice').value) || 0;
        const profit = Math.max(0, salePrice - currentWholesale);
        const margin = currentWholesale > 0 ? Math.round((profit / currentWholesale) * 100) : 0;

        document.getElementById('modalCalculatedProfit').textContent = '$ ' + Math.round(profit).toLocaleString('es-CO') + ' COP';
        document.getElementById('modalCalculatedMargin').textContent = margin + '% Margen';
    }

    function calcCustomProfit() {
        const wholesale = parseFloat(document.getElementById('customWholesalePrice').value) || 0;
        const sale = parseFloat(document.getElementById('customSalePrice').value) || 0;
        const profit = Math.max(0, sale - wholesale);
        const margin = wholesale > 0 ? Math.round((profit / wholesale) * 100) : 0;

        document.getElementById('customCalculatedProfit').textContent = '$ ' + Math.round(profit).toLocaleString('es-CO') + ' COP';
        document.getElementById('customCalculatedMargin').textContent = margin + '% Margen';
    }
</script>
@endpush
