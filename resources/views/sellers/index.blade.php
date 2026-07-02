@extends('layout')

@section('title', 'Vendedores')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.6rem; font-weight:700; color:var(--text-main);">Gestión de Vendedores</h2>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.25rem;">Administra los vendedores y revisa sus métricas del mes actual.</p>
    </div>
    <div style="display:flex; gap:1rem;">
        <button onclick="openModal('modal-create-seller')" class="filter-btn-primary" style="gap:0.4rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo Vendedor
        </button>
    </div>
</div>

<div class="page-card">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Documento / Teléfono</th>
                    <th>Facturado (Mes Actual)</th>
                    <th>Recaudado (Mes Actual)</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sellers as $seller)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <div style="width:36px; height:36px; background:rgba(79,70,229,0.1); border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div>
                                <span style="font-weight:600; color:var(--text-main); display:block;">{{ $seller->name }}</span>
                                <span style="font-size:0.75rem; color:var(--text-muted);">{{ $seller->invoices_count }} Facturas asignadas</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:0.85rem; color:var(--text-muted); display:flex; flex-direction:column; gap:0.25rem;">
                            @if($seller->document_id)
                            <div style="display:flex; align-items:center; gap:0.35rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $seller->document_id }}
                            </div>
                            @endif
                            @if($seller->phone)
                            <div style="display:flex; align-items:center; gap:0.35rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                {{ $seller->phone }}
                            </div>
                            @endif
                            @if(!$seller->document_id && !$seller->phone)
                            <span>-</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span style="font-weight:700; color:var(--text-main);">{{ dinero($seller->invoiced_this_month) }}</span>
                    </td>
                    <td>
                        <span style="font-weight:700; color:#10b981;">{{ dinero($seller->collected_this_month) }}</span>
                    </td>
                    <td>
                        @if($seller->status === 'activo')
                            <span class="badge pagada">Activo</span>
                        @else
                            <span class="badge anulada">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions-group">
                            <button type="button" class="action-btn" title="Editar" onclick="editSeller({{ $seller }})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            @if($seller->invoices_count === 0)
                            <form action="{{ route('sellers.destroy', $seller) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Eliminar este vendedor?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn text-danger" title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:3rem; color:var(--text-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:1rem; opacity:0.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <p style="margin:0;">No hay vendedores registrados.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL CREAR --}}
<div id="modal-create-seller" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title">Nuevo Vendedor</div>
                <div class="modal-subtitle">Registra un nuevo vendedor en el sistema.</div>
            </div>
            <button onclick="closeModal('modal-create-seller')" class="modal-close-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form action="{{ route('sellers.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-size:0.75rem;">Nombre del Vendedor <span style="color:#ef4444">*</span></label>
                <input type="text" name="name" class="form-input" required placeholder="Ej: Janine Villamizar" style="width:100%; box-sizing:border-box;">
            </div>
            <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
                <div class="form-group" style="flex:1; min-width:140px;">
                    <label class="form-label" style="font-size:0.75rem;">Documento</label>
                    <input type="text" name="document_id" class="form-input" placeholder="Ej: 1090..." style="width:100%; box-sizing:border-box;">
                </div>
                <div class="form-group" style="flex:1; min-width:140px;">
                    <label class="form-label" style="font-size:0.75rem;">Teléfono</label>
                    <input type="text" name="phone" class="form-input" placeholder="Ej: 300..." style="width:100%; box-sizing:border-box;">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label class="form-label" style="font-size:0.75rem;">Estado</label>
                <select name="status" class="form-input" style="width:100%; box-sizing:border-box; cursor:pointer;">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="filter-btn-clear" onclick="closeModal('modal-create-seller')">Cancelar</button>
                <button type="submit" class="filter-btn-primary">Guardar Vendedor</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDITAR --}}
<div id="modal-edit-seller" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title">Editar Vendedor</div>
                <div class="modal-subtitle">Modifica la información del vendedor seleccionado.</div>
            </div>
            <button onclick="closeModal('modal-edit-seller')" class="modal-close-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="form-edit-seller" method="POST">
            @csrf @method('PUT')
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-size:0.75rem;">Nombre del Vendedor <span style="color:#ef4444">*</span></label>
                <input type="text" name="name" id="edit-seller-name" class="form-input" required style="width:100%; box-sizing:border-box;">
            </div>
            <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
                <div class="form-group" style="flex:1; min-width:140px;">
                    <label class="form-label" style="font-size:0.75rem;">Documento</label>
                    <input type="text" name="document_id" id="edit-seller-document" class="form-input" style="width:100%; box-sizing:border-box;">
                </div>
                <div class="form-group" style="flex:1; min-width:140px;">
                    <label class="form-label" style="font-size:0.75rem;">Teléfono</label>
                    <input type="text" name="phone" id="edit-seller-phone" class="form-input" style="width:100%; box-sizing:border-box;">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label class="form-label" style="font-size:0.75rem;">Estado</label>
                <select name="status" id="edit-seller-status" class="form-input" style="width:100%; box-sizing:border-box; cursor:pointer;">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="filter-btn-clear" onclick="closeModal('modal-edit-seller')">Cancelar</button>
                <button type="submit" class="filter-btn-primary">Actualizar Vendedor</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSeller(seller) {
    document.getElementById('form-edit-seller').action = `/sellers/${seller.id}`;
    document.getElementById('edit-seller-name').value = seller.name || '';
    document.getElementById('edit-seller-document').value = seller.document_id || '';
    document.getElementById('edit-seller-phone').value = seller.phone || '';
    document.getElementById('edit-seller-status').value = seller.status || 'activo';
    openModal('modal-edit-seller');
}
</script>
@endsection
