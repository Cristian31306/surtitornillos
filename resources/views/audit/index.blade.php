@extends('layout')

@section('title', 'Bitácora de Auditoría')

@section('content')

<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2 style="font-size:1.6rem; font-weight:700; color:var(--text-main);">Auditoría de Actividad</h2>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.25rem;">Consulta y monitorea las acciones y cambios realizados en el sistema.</p>
    </div>
</div>

{{-- Barra de Filtros --}}
<div class="filter-panel">
    <form method="GET" action="{{ route('audit.index') }}" class="filter-panel-form">
        <div class="filter-panel-fields" style="grid-template-columns: 1fr 1fr;">
            <div class="filter-field">
                <label class="filter-label">Buscar descripción o usuario</label>
                <div class="filter-input-wrap">
                    <svg class="filter-search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="search" value="{{ $search }}" class="filter-search-input" placeholder="Ej: F-1023, cristian, abono...">
                </div>
            </div>
            <div class="filter-field">
                <label class="filter-label">Tipo de Evento</label>
                <div class="filter-input-wrap">
                    <select name="event_type" class="filter-search-input filter-select" style="cursor:pointer;">
                        <option value="">Todos los eventos</option>
                        <option value="creacion_cliente" {{ $event === 'creacion_cliente' ? 'selected':'' }}>Creación de Cliente</option>
                        <option value="edicion_cliente" {{ $event === 'edicion_cliente' ? 'selected':'' }}>Edición de Cliente</option>
                        <option value="creacion_factura" {{ $event === 'creacion_factura' ? 'selected':'' }}>Creación de Factura</option>
                        <option value="edicion_factura" {{ $event === 'edicion_factura' ? 'selected':'' }}>Edición de Factura</option>
                        <option value="anulacion_factura" {{ $event === 'anulacion_factura' ? 'selected':'' }}>Cambio de Estado/Anulación</option>
                        <option value="abono_registrado" {{ $event === 'abono_registrado' ? 'selected':'' }}>Abono Registrado</option>
                        <option value="abono_eliminado" {{ $event === 'abono_eliminado' ? 'selected':'' }}>Abono Eliminado</option>
                        <option value="ajuste_registrado" {{ $event === 'ajuste_registrado' ? 'selected':'' }}>Ajuste Registrado</option>
                        <option value="ajuste_eliminado" {{ $event === 'ajuste_eliminado' ? 'selected':'' }}>Ajuste Eliminado</option>
                        <option value="creacion_usuario" {{ $event === 'creacion_usuario' ? 'selected':'' }}>Creación de Usuario</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="filter-panel-actions">
            <button type="submit" class="filter-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Buscar
            </button>
            @if($search || $event)
                <a href="{{ route('audit.index') }}" class="filter-btn-clear">Limpiar filtros</a>
            @endif
        </div>
    </form>
</div>

{{-- Tabla de Logs --}}
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th style="width:180px;">Fecha y Hora</th>
                <th style="width:150px;">Usuario</th>
                <th style="width:160px;">Evento</th>
                <th>Descripción de la acción</th>
                <th style="width:130px; text-align:center;">Dirección IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="white-space:nowrap; color:var(--text-muted); font-size:0.85rem;">
                    {{ \Carbon\Carbon::parse($log->created_at)->timezone('America/Bogota')->format('d/m/Y h:i A') }}
                </td>
                <td>
                    <strong style="color:var(--text-main)">{{ $log->user ? $log->user->name : 'Sistema/Desconocido' }}</strong>
                    <span style="font-size:0.75rem; color:var(--text-muted); display:block;">@ {{$log->user ? $log->user->username : 'system'}}</span>
                </td>
                <td>
                    <span class="badge" style="font-size:0.75rem; text-transform:uppercase; background:#f1f5f9; color:var(--text-muted); border:1px solid #e2e8f0;">
                        {{ str_replace('_', ' ', $log->event_type) }}
                    </span>
                </td>
                <td style="font-size:0.9rem; line-height:1.4;">{{ $log->description }}</td>
                <td style="font-size:0.8rem; color:var(--text-muted); text-align:center; font-family:monospace;">
                    {{ $log->ip_address ?: '127.0.0.1' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty-state">No se registraron acciones con los filtros actuales.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination-wrapper" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-top:1.5rem;">
    <div style="font-size:0.875rem; color:var(--text-muted);">
        Mostrando <strong>{{ $logs->firstItem() ?? 0 }}</strong> a <strong>{{ $logs->lastItem() ?? 0 }}</strong> de <strong>{{ $logs->total() }}</strong> registros (Página {{ $logs->currentPage() }} de {{ $logs->lastPage() }})
    </div>
    <div>
        {{ $logs->links('vendor.pagination.simple') }}
    </div>
</div>

@endsection
