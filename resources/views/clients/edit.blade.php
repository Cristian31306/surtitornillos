@extends('layout')

@section('title', 'Editar Cliente')

@section('content')

<div style="max-width:560px; margin:0 auto;">
    {{-- Breadcrumb --}}
    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('clients.index') }}" style="font-size:0.85rem; color:var(--text-muted); text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; transition:color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Volver a Clientes
        </a>
        <h1 style="font-size:1.6rem; font-weight:700; color:var(--text-main); margin-top:0.5rem;">Editar Datos de Cliente</h1>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.25rem;">Modifica la información básica del cliente.</p>
    </div>

    {{-- Tarjeta del formulario --}}
    <div class="page-card" style="padding:2rem;">
        <form method="POST" action="{{ route('clients.update', $client) }}">
            @csrf
            @method('PUT')

            <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label" style="font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.5rem;">
                    Nombre del Cliente <span style="color:var(--danger)">*</span>
                </label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $client->name) }}"
                    placeholder="Ej: Juan Pérez" required autofocus
                    style="width:100%; box-sizing:border-box;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div class="form-group">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.5rem;">
                        Cédula o NIT
                    </label>
                    <input type="text" name="document_id" class="form-input" value="{{ old('document_id', $client->document_id) }}"
                        placeholder="Ej: 1098765432"
                        style="width:100%; box-sizing:border-box;">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.5rem;">
                        Teléfono
                    </label>
                    <input type="text" name="phone" class="form-input" value="{{ old('phone', $client->phone) }}"
                        placeholder="Ej: 3001234567"
                        style="width:100%; box-sizing:border-box;">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem; padding-top:1.25rem; border-top:1px solid var(--surface-border); margin-top:0.5rem;">
                <a href="{{ route('clients.index') }}" class="filter-btn-clear" style="text-decoration:none;">
                    Cancelar
                </a>
                <button type="submit" class="filter-btn-primary" style="padding:0.65rem 1.75rem; font-size:0.9rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
