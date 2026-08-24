@extends('layouts.admin')

@section('title', 'Editar Producto')
@section('page_header', 'Editar: ' . $product->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h5 class="fw-bold mb-0 text-dark">Editar Información del Producto</h5>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Volver a la lista
                </a>
            </div>

            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    <!-- Name -->
                    <div class="col-md-8">
                        <label for="name" class="form-label fw-semibold small">Nombre del Producto *</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="col-md-4">
                        <label for="category_id" class="form-label fw-semibold small">Categoría *</label>
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Selecciona una categoría</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- SKU & Badge -->
                    <div class="col-md-6">
                        <label for="sku" class="form-label fw-semibold small">SKU / Código Único</label>
                        <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}">
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="badge" class="form-label fw-semibold small">Etiqueta Promocional (Badge)</label>
                        <input type="text" name="badge" id="badge" class="form-control" value="{{ old('badge', $product->badge) }}" placeholder="Ej: NUEVO, OFERTA, TOP VENTAS">
                    </div>

                    <!-- Prices & Stock -->
                    <div class="col-md-4">
                        <label for="price" class="form-label fw-semibold small">Precio de Venta ($) *</label>
                        <input type="number" step="0.01" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $product->price) }}" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="compare_price" class="form-label fw-semibold small">Precio Anterior / Comparación ($)</label>
                        <input type="number" step="0.01" name="compare_price" id="compare_price" class="form-control" value="{{ old('compare_price', $product->compare_price) }}">
                    </div>

                    <div class="col-md-4">
                        <label for="stock" class="form-label fw-semibold small">Existencias (Stock) *</label>
                        <input type="number" name="stock" id="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $product->stock) }}" required min="0">
                        @error('stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Image URL -->
                    <div class="col-md-12">
                        <label for="image" class="form-label fw-semibold small">URL de Imagen del Producto</label>
                        <div class="input-group">
                            <input type="url" name="image" id="image" class="form-control @error('image') is-invalid @enderror" value="{{ old('image', $product->image) }}">
                            @if($product->image)
                                <a href="{{ $product->image }}" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-image"></i> Ver</a>
                            @endif
                        </div>
                    </div>

                    <!-- Short Description -->
                    <div class="col-12">
                        <label for="short_description" class="form-label fw-semibold small">Descripción Corta</label>
                        <input type="text" name="short_description" id="short_description" class="form-control" value="{{ old('short_description', $product->short_description) }}">
                    </div>

                    <!-- Full Description -->
                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold small">Descripción Completa</label>
                        <textarea name="description" id="description" rows="5" class="form-control">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <!-- Toggles -->
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold small" for="is_featured">Marcar como Producto Destacado en Inicio</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold small" for="is_active">Producto Activo y Visible en la Tienda</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
