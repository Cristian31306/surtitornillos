@extends('layout')

@section('title', 'Control de Usuarios')

@section('content')

@php
    $memStart   = \App\Models\Setting::get('membership_starts_at', now()->format('Y-m-d'));
    $memExpires = \App\Models\Setting::get('membership_expires_at', now()->addYear()->format('Y-m-d'));
    $startDate  = \Carbon\Carbon::parse($memStart);
    $expiresDate= \Carbon\Carbon::parse($memExpires);
    $today      = \Carbon\Carbon::today();
    $totalDays  = max($startDate->diffInDays($expiresDate), 1);
    $elapsedDays= max(0, min($startDate->diffInDays($today), $totalDays));
    $daysLeft   = max(0, $today->diffInDays($expiresDate, false));
    $pct        = round(($elapsedDays / $totalDays) * 100, 1);
    $isExpired  = $today->greaterThan($expiresDate);
    $barColor   = $daysLeft > 30 ? '#22c55e' : ($daysLeft > 7 ? '#f59e0b' : '#dc2626');
@endphp

{{-- ===== PANEL DE MEMBRESÍA (SOLO ADMIN) ===== --}}
<div class="page-card" style="padding:1.75rem 2rem; margin-bottom:2rem; border:1px solid {{ $isExpired ? '#fecaca' : 'var(--surface-border)' }}; {{ $isExpired ? 'background:#fff5f5;' : '' }}">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem; margin-bottom:1.25rem;">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <div style="width:38px; height:38px; background:{{ $isExpired ? '#fef2f2' : 'rgba(79,70,229,0.08)' }}; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $isExpired ? '#dc2626' : 'var(--primary)' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div>
                <h3 style="font-size:1rem; font-weight:700; color:var(--text-main); margin:0;">Control de Membresía</h3>
                <p style="font-size:0.8rem; color:var(--text-muted); margin:0;">Período de licencia activo de la plataforma.</p>
            </div>
        </div>
        <div style="display:flex; gap:2rem; flex-wrap:wrap;">
            <div style="text-align:center;">
                <div style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); font-weight:600; margin-bottom:0.2rem;">Inicio</div>
                <div style="font-size:0.95rem; font-weight:700; color:var(--text-main);">{{ fecha_co($memStart) }}</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); font-weight:600; margin-bottom:0.2rem;">Vencimiento</div>
                <div style="font-size:0.95rem; font-weight:700; color:{{ $isExpired ? '#dc2626' : ($daysLeft <= 30 ? '#d97706' : 'var(--text-main)') }};">{{ fecha_co($memExpires) }}</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); font-weight:600; margin-bottom:0.2rem;">Estado</div>
                @if($isExpired)
                    <span class="badge anulada" style="font-size:0.8rem;">Vencida</span>
                @elseif($daysLeft <= 7)
                    <span class="badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;font-size:0.8rem;">{{ $daysLeft }} días</span>
                @elseif($daysLeft <= 30)
                    <span class="badge pendiente" style="font-size:0.8rem;">{{ $daysLeft }} días</span>
                @else
                    <span class="badge pagada" style="font-size:0.8rem;">{{ $daysLeft }} días restantes</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Barra de progreso del período --}}
    <div style="margin-bottom:1.25rem;">
        <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:var(--text-muted); margin-bottom:0.4rem;">
            <span>{{ $elapsedDays }} días transcurridos</span>
            <span>{{ $totalDays }} días totales</span>
        </div>
        <div style="height:8px; background:#e2e8f0; border-radius:99px; overflow:hidden;">
            <div style="height:100%; width:{{ min($pct, 100) }}%; background:{{ $barColor }}; border-radius:99px; transition:width 0.4s;"></div>
        </div>
    </div>

    {{-- Formulario para actualizar fechas --}}
    <form method="POST" action="{{ route('settings.membership') }}" style="display:flex; align-items:flex-end; gap:1rem; flex-wrap:wrap; padding-top:1.25rem; border-top:1px solid var(--surface-border);">
        @csrf
        <div style="flex:1; min-width:160px;">
            <label style="font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.35rem;">Fecha de Inicio</label>
            <input type="date" name="membership_starts_at" class="form-input" value="{{ $memStart }}" style="width:100%; box-sizing:border-box;">
        </div>
        <div style="flex:1; min-width:160px;">
            <label style="font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.35rem;">Fecha de Vencimiento</label>
            <input type="date" name="membership_expires_at" class="form-input" value="{{ $memExpires }}" style="width:100%; box-sizing:border-box;">
        </div>
        <button type="submit" class="filter-btn-primary" style="padding:0.65rem 1.5rem; white-space:nowrap;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Guardar Membresía
        </button>
    </form>
</div>
{{-- ============================================= --}}

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.6rem; font-weight:700; color:var(--text-main);">Gestión de Usuarios</h2>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.25rem;">Crea, edita y administra los permisos de acceso al sistema.</p>
    </div>
    <button onclick="openModal('modal-create-user')" class="filter-btn-primary" style="gap:0.4rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nuevo Usuario
    </button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Usuario (Login)</th>
                <th>Nombre Completo</th>
                <th>Rol</th>
                <th>Creado el</th>
                <th style="width:120px; text-align:center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            <tr>
                <td><strong>{{ $u->username }}</strong></td>
                <td>{{ $u->name ?: '—' }}</td>
                <td>
                    <span class="badge" style="background:{{ $u->isAdmin() ? 'rgba(79, 70, 229, 0.1)' : 'rgba(100, 116, 139, 0.1)' }}; color:{{ $u->isAdmin() ? 'var(--primary)' : 'var(--text-muted)' }}; border: 1px solid {{ $u->isAdmin() ? 'rgba(79, 70, 229, 0.2)' : 'rgba(100, 116, 139, 0.2)' }};">
                        {{ ucfirst($u->role) }}
                    </span>
                </td>
                <td style="color:var(--text-muted); font-size:0.85rem;">{{ fecha_co($u->created_at) }}</td>
                <td style="text-align:center;">
                    <div style="display:inline-flex; gap:0.5rem;">
                        <button onclick="editUser({{ json_encode($u) }})" class="btn-sm" style="background:#fff; border:1px solid #cbd5e1; color:var(--text-main); cursor:pointer;">Editar</button>
                        @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('¿Seguro de eliminar este usuario?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-sm btn-danger">Eliminar</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- MODAL: Crear Usuario --}}
<div id="modal-create-user" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title">Registrar Usuario</div>
                <div class="modal-subtitle">Añade un nuevo colaborador al sistema.</div>
            </div>
            <button class="modal-close-btn" onclick="closeModal('modal-create-user')">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-size:0.75rem;">Usuario (tipo: cristian, alexander) *</label>
                <input type="text" name="username" class="form-input" required style="width:100%; box-sizing:border-box;">
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-size:0.75rem;">Nombre Completo *</label>
                <input type="text" name="name" class="form-input" required style="width:100%; box-sizing:border-box;">
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-size:0.75rem;">Contraseña *</label>
                <div style="position:relative;">
                    <input type="password" name="password" id="pass-create" class="form-input" required style="width:100%; box-sizing:border-box; padding-right:2.5rem;">
                    <button type="button" onclick="togglePass('pass-create')" style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-muted); display:flex; align-items:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label class="form-label" style="font-size:0.75rem;">Rol</label>
                <select name="role" class="form-input" style="width:100%; box-sizing:border-box; cursor:pointer;">
                    <option value="colaborador">Colaborador (Vistas estándar)</option>
                    <option value="admin">Administrador (Control total + Auditoría)</option>
                </select>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="filter-btn-clear" onclick="closeModal('modal-create-user')">Cancelar</button>
                <button type="submit" class="filter-btn-primary">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: Editar Usuario --}}
<div id="modal-edit-user" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title">Editar Usuario <span id="edit-user-title" style="color:var(--primary)"></span></div>
                <div class="modal-subtitle">Modifica la información o asigna una nueva contraseña.</div>
            </div>
            <button class="modal-close-btn" onclick="closeModal('modal-edit-user')">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="form-edit-user" method="POST" action="">
            @csrf @method('PUT')
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-size:0.75rem;">Nombre Completo *</label>
                <input type="text" name="name" id="edit-name" class="form-input" required style="width:100%; box-sizing:border-box;">
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label" style="font-size:0.75rem;">Nueva Contraseña (Dejar vacío para no cambiar)</label>
                <div style="position:relative;">
                    <input type="password" name="password" id="pass-edit" class="form-input" style="width:100%; box-sizing:border-box; padding-right:2.5rem;">
                    <button type="button" onclick="togglePass('pass-edit')" style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-muted); display:flex; align-items:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label class="form-label" style="font-size:0.75rem;">Rol</label>
                <select name="role" id="edit-role" class="form-input" style="width:100%; box-sizing:border-box; cursor:pointer;">
                    <option value="colaborador">Colaborador</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="filter-btn-clear" onclick="closeModal('modal-edit-user')">Cancelar</button>
                <button type="submit" class="filter-btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePass(id) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}

function editUser(user) {
    document.getElementById('edit-user-title').innerText = `@${user.username}`;
    document.getElementById('edit-name').value = user.name || '';
    document.getElementById('edit-role').value = user.role;
    document.getElementById('pass-edit').value = '';
    
    // Configurar acción dinámica en el formulario
    const actionUrl = `{{ url('/usuarios') }}/${user.id}`;
    document.getElementById('form-edit-user').action = actionUrl;
    
    openModal('modal-edit-user');
}
</script>

@endsection
