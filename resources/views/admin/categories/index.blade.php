@extends('layouts.admin')

@section('title', 'Gestión de Categorías')
@section('page_header', 'Categorías de la Tienda')

@section('content')
<div class="row g-4">
    <!-- Categories List -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark">Listado de Categorías</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light small text-muted text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Categoría</th>
                            <th class="py-3">Slug</th>
                            <th class="py-3 text-center">Productos</th>
                            <th class="py-3 text-center">Destacada</th>
                            <th class="pe-4 py-3 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr class="border-bottom">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi {{ $category->icon ?: 'bi-tag' }} fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $category->name }}</h6>
                                            <small class="text-muted text-truncate d-block" style="max-width: 200px;">{{ $category->description }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 small font-monospace text-muted">{{ $category->slug }}</td>
                                <td class="py-3 text-center">
                                    <span class="badge bg-light text-dark border">{{ $category->products_count }}</span>
                                </td>
                                <td class="py-3 text-center">
                                    @if($category->is_featured)
                                        <span class="badge bg-warning-subtle text-warning border border-warning">Sí</span>
                                    @else
                                        <span class="badge bg-light text-muted">No</span>
                                    @endif
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <div class="btn-group btn-group-sm">
                                        <!-- Edit Modal Trigger -->
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta categoría?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Edit Modal -->
                                    <div class="modal fade text-start" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold">Editar Categoría</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Nombre *</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Icono (Bootstrap Icons)</label>
                                                            <input type="text" name="icon" class="form-control" value="{{ $category->icon }}" placeholder="bi-laptop">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Descripción</label>
                                                            <textarea name="description" class="form-control" rows="3">{{ $category->description }}</textarea>
                                                        </div>
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featured_{{ $category->id }}" {{ $category->is_featured ? 'checked' : '' }}>
                                                            <label class="form-check-label small fw-semibold" for="featured_{{ $category->id }}">Destacar en Página Principal</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No hay categorías registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $categories->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Create Category Form -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">Nueva Categoría</h5>
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="new_name" class="form-label fw-semibold small">Nombre de la Categoría *</label>
                    <input type="text" name="name" id="new_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Ej: Electrónica & Audio">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="new_icon" class="form-label fw-semibold small">Clase de Icono (Bootstrap Icons)</label>
                    <input type="text" name="icon" id="new_icon" class="form-control" value="{{ old('icon', 'bi-tag') }}" placeholder="bi-headphones, bi-laptop, bi-bag...">
                    <small class="text-muted">Ejemplo: bi-laptop, bi-tag, bi-fire, bi-headphones</small>
                </div>

                <div class="mb-3">
                    <label for="new_desc" class="form-label fw-semibold small">Descripción</label>
                    <textarea name="description" id="new_desc" rows="3" class="form-control" placeholder="Breve descripción...">{{ old('description') }}</textarea>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="new_featured" {{ old('is_featured') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold small" for="new_featured">Destacar en Página Principal</label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i> Crear Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
