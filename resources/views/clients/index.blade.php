@extends('layout')

@section('title', 'Clientes')

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Toolbar mejorado --}}
    <div class="page-toolbar">
        <form method="GET" action="{{ route('clients.index') }}" class="filter-bar">
            <div class="filter-search-box">
                <svg class="filter-search-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" name="search" value="{{ $search }}" class="filter-search-input"
                    placeholder="Buscar cliente por nombre..." autofocus>
                @if($search)
                    <a href="{{ route('clients.index') }}" class="filter-clear-btn" title="Limpiar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </a>
                @endif
            </div>
            <button type="submit" class="filter-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                Buscar
            </button>
            <a href="{{ route('clients.create') }}" class="filter-btn-primary"
                style="margin-left:auto; text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Nuevo Cliente
            </a>
        </form>
        <div class="page-toolbar-meta">
            <span class="result-count">
                <strong>{{ $clients->total() }}</strong> clientes
                @if($search) que coinciden con "<em>{{ $search }}</em>" @endif
            </span>
        </div>
    </div>

    <div class="table-container">
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Nombre del Cliente</th>
                        <th>Teléfono</th>
                        <th>Documento</th>
                        <th>Facturas</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="client-avatar">{{ strtoupper(substr($client->name, 0, 1)) }}</div>
                                    <strong>{{ $client->name }}</strong>
                                </div>
                            </td>
                            <td>
                                @if($client->phone)
                                    <span style="display:flex;align-items:center;gap:5px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" style="color:var(--text-muted)">
                                            <path
                                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.5a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.72h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 10.2a16 16 0 0 0 5.89 5.89l.93-.93a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.28 18z" />
                                        </svg>
                                        {{ $client->phone }}
                                    </span>
                                @else
                                    <span class="empty-tag">— Sin teléfono</span>
                                @endif
                            </td>
                            <td>
                                @if($client->document_id)
                                    <code
                                        style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:0.85rem;">{{ $client->document_id }}</code>
                                @else
                                    <span class="empty-tag">— Sin documento</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('invoices.index', ['cliente' => $client->name]) }}"
                                    class="invoice-count-link">
                                    <span class="invoice-count-badge">{{ $client->invoices_count }}</span>
                                    <span>factura(s)</span>
                                </a>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('clients.edit', $client) }}" class="btn-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
                                    style="color:var(--text-muted);display:block;margin:0 auto 0.75rem">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                </svg>
                                No se encontraron clientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-wrapper"
        style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div style="font-size:0.875rem; color:var(--text-muted);">
            Mostrando <strong>{{ $clients->firstItem() ?? 0 }}</strong> a <strong>{{ $clients->lastItem() ?? 0 }}</strong>
            de <strong>{{ $clients->total() }}</strong> clientes (Página {{ $clients->currentPage() }} de
            {{ $clients->lastPage() }})
        </div>
        <div>
            {{ $clients->links('vendor.pagination.simple') }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('.filter-bar');
            if (!form) return;

            let timeout = null;
            const input = form.querySelector('input[name="search"]');
            if (input) {
                // Al recargar la página, mover el cursor al final si hay texto
                if (input.value.length > 0) {
                    const len = input.value.length;
                    input.setSelectionRange(len, len);
                }

                input.addEventListener('input', function () {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        form.submit();
                    }, 600); // 600ms de espera antes de aplicar el filtro
                });
            }
        });
    </script>
@endsection