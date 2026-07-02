<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Surtitornillos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
    /* ─── Toast Notifications ─────────────────── */
    #toast-container {
        position: fixed;
        bottom: 1.5rem;
        left: 1.5rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        pointer-events: none;
    }
    .toast {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #4f46e5;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        min-width: 280px;
        max-width: 380px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.12);
        pointer-events: all;
        animation: toastIn 0.35s cubic-bezier(0.16,1,0.3,1) both;
    }
    .toast.toast-success { border-left-color: #10b981; }
    .toast.toast-error   { border-left-color: #ef4444; }
    .toast.toast-warning { border-left-color: #f59e0b; }
    .toast-icon { flex-shrink:0; margin-top:1px; }
    .toast-body { flex:1; }
    .toast-title { font-size: 0.875rem; font-weight: 600; color: #0f172a; margin-bottom: 0.15rem; }
    .toast-msg   { font-size: 0.8rem; color: #64748b; line-height: 1.4; }
    .toast-close {
        flex-shrink:0; background:none; border:none; cursor:pointer;
        color:#94a3b8; font-size:1rem; line-height:1; padding:0; margin-top:1px;
        transition: color 0.2s;
    }
    .toast-close:hover { color: #0f172a; }
    .toast-progress {
        position: absolute;
        bottom: 0; left: 0;
        height: 3px;
        border-radius: 0 0 0 8px;
        animation: toastProgress 4.5s linear forwards;
    }
    @keyframes toastIn {
        from { opacity: 0; transform: translateX(-30px) scale(0.95); }
        to   { opacity: 1; transform: translateX(0) scale(1); }
    }
    @keyframes toastProgress {
        from { width: 100%; }
        to   { width: 0%; }
    }

    /* ─── Modal global ────────────────────────── */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.45);
        z-index: 8000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(3px);
        animation: fadeInOverlay 0.2s ease;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
        background: #fff;
        border-radius: 16px;
        padding: 2rem;
        width: 100%;
        max-width: 460px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.2);
        animation: slideUpModal 0.3s cubic-bezier(0.16,1,0.3,1);
        position: relative;
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .modal-title { font-size: 1.125rem; font-weight: 700; color: #0f172a; }
    .modal-subtitle { font-size: 0.8rem; color: #64748b; margin-top: 0.25rem; }
    .modal-close-btn {
        background: none; border: none; cursor: pointer;
        color: #94a3b8; font-size: 1.25rem; padding: 0;
        transition: color 0.2s;
    }
    .modal-close-btn:hover { color: #0f172a; }
    @keyframes fadeInOverlay {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    @keyframes slideUpModal {
        from { opacity: 0; transform: translateY(20px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    </style>
</head>
<body>
    <div id="toast-container"></div>

    @auth
        <div class="app-container">
            <aside class="sidebar">
                <div class="sidebar-header">
                    <h2>Surtitornillos</h2>
                </div>
                <nav class="sidebar-nav">
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:middle"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('clients.index') }}" class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:middle"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Clientes
                    </a>
                    <a href="{{ route('invoices.index') }}" class="nav-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:middle"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        Facturas
                    </a>

                    @if(auth()->user()->isAdmin())
                        <div style="margin: 1.5rem 0.8rem 0.5rem 0.8rem; padding-top: 0.8rem; border-top: 1px solid rgba(226, 232, 240, 0.4); font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em;">
                            Administración
                        </div>
                        <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:middle"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Usuarios
                        </a>
                        <a href="{{ route('audit.index') }}" class="nav-item {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:middle"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            Auditoría (Logs)
                        </a>
                    @endif
                </nav>
                <div class="sidebar-footer">
                    <div class="user-info">
                        <span class="user-name">{{ auth()->user()->name ?: auth()->user()->username }}</span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-logout">Cerrar Sesión</button>
                    </form>
                </div>
            </aside>
            <main class="main-content">
                <header class="topbar">
                    <h2 class="page-title">@yield('title', 'Dashboard')</h2>
                </header>
                <div class="content-wrapper">
                    @php
                        $__expires  = \App\Models\Setting::get('membership_expires_at');
                        $__daysLeft = $__expires ? (int) \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($__expires), false) : null;
                        $__isExpired = $__expires && \Carbon\Carbon::today()->greaterThan(\Carbon\Carbon::parse($__expires));
                    @endphp

                    {{-- Banner de aviso de membresía solo para colaboradores --}}
                    @if(!auth()->user()->isAdmin() && $__expires)
                        @if($__isExpired)
                            <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:0.75rem 1.25rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.75rem; font-size:0.875rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <span><strong style="color:#dc2626;">La suscripción del sistema ha vencido.</strong> Por favor, contacta al administrador para renovarla.</span>
                            </div>
                        @elseif($__daysLeft !== null && $__daysLeft <= 30)
                            <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:0.75rem 1.25rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.75rem; font-size:0.875rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                <span style="color:#92400e;"><strong>Aviso:</strong> La suscripción de este sistema vence en <strong>{{ $__daysLeft }} día{{ $__daysLeft !== 1 ? 's' : '' }}</strong> ({{ fecha_co($__expires) }}). Contacta al administrador para renovarla.</span>
                            </div>
                        @endif
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    @else
        @yield('content')
    @endauth

    <script>
    /* ─── Sistema de Toasts ─────────────────────── */
    (function() {
        const icons = {
            success: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
            error:   `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
            warning: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
            info:    `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
        };
        const titles   = { success: 'Exitoso', error: 'Error', warning: 'Atención', info: 'Información' };
        const colors   = { success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#4f46e5' };

        window.showToast = function(message, type = 'success', duration = 4500) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <span class="toast-icon">${icons[type] || icons.info}</span>
                <div class="toast-body">
                    <div class="toast-title">${titles[type] || 'Notificación'}</div>
                    <div class="toast-msg">${message}</div>
                </div>
                <button class="toast-close" onclick="dismissToast(this.parentElement)" title="Cerrar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <div class="toast-progress" style="background:${colors[type]||colors.info}; animation-duration:${duration}ms;"></div>
            `;
            container.appendChild(toast);
            setTimeout(() => dismissToast(toast), duration);
        };

        window.dismissToast = function(toast) {
            if (!toast || toast._dismissing) return;
            toast._dismissing = true;
            toast.style.transition = 'opacity 0.3s, transform 0.3s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-20px)';
            setTimeout(() => toast.remove(), 300);
        };

        /* Función global para abrir/cerrar modals */
        window.openModal = function(id) {
            const m = document.getElementById(id);
            if (m) m.classList.add('active');
        };
        window.closeModal = function(id) {
            const m = document.getElementById(id);
            if (m) m.classList.remove('active');
        };
        /* Cerrar modal al hacer clic fuera */
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                e.target.classList.remove('active');
            }
        });

        /* Disparar flash messages de Laravel como toasts */
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                showToast(@json(session('success')), 'success');
            @endif
            @if(session('error'))
                showToast(@json(session('error')), 'error');
            @endif
            @if(session('warning'))
                showToast(@json(session('warning')), 'warning');
            @endif
            @if(session('info'))
                showToast(@json(session('info')), 'info');
            @endif
            @if($errors->any())
                @foreach($errors->all() as $err)
                    showToast(@json($err), 'error');
                @endforeach
            @endif

            /* Inicializar TomSelect para selects buscables */
            document.querySelectorAll('.searchable-select').forEach(function(el) {
                new TomSelect(el, {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });
            });
        });
    })();
    </script>
</body>
</html>
