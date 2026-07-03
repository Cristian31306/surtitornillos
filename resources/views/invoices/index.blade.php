@extends('layout')

@section('title', 'Cartera de Facturas')

@section('content')

    {{-- Panel de Filtros --}}
    <div class="filter-panel">
        <form method="GET" action="{{ route('invoices.index') }}" class="filter-panel-form">
            <div class="filter-panel-fields">
                <div class="filter-field">
                    <label class="filter-label">Número de Factura</label>
                    <div class="filter-input-wrap">
                        <svg class="filter-search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        </svg>
                        <input type="text" name="search" value="{{ $search }}" class="filter-search-input"
                            placeholder="Ej: F-001">
                    </div>
                </div>
                <div class="filter-field">
                    <label class="filter-label">Cliente</label>
                    <div class="filter-input-wrap">
                        <svg class="filter-search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                        </svg>
                        <input type="text" name="cliente" value="{{ $cliente }}" class="filter-search-input"
                            placeholder="Nombre del cliente...">
                    </div>
                </div>
                <div class="filter-field">
                    <label class="filter-label">Estado</label>
                    <div class="filter-input-wrap">
                        <svg class="filter-search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <select name="status" class="filter-search-input filter-select">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" {{ $status === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="pagada" {{ $status === 'pagada' ? 'selected' : '' }}>Pagada</option>
                            <option value="anulada" {{ $status === 'anulada' ? 'selected' : '' }}>Anulada</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-panel-actions">
                <button type="button" class="filter-btn-primary" onclick="document.getElementById('export-payments-panel').style.display = document.getElementById('export-payments-panel').style.display === 'none' ? 'block' : 'none';" style="background-color: var(--primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Recibos de caja detallados por factura
                </button>
                @if($search || $status || $cliente)
                    <a href="{{ route('invoices.index') }}" class="filter-btn-clear">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                        Limpiar filtros
                    </a>
                @endif
                <span class="result-count">
                    <strong>{{ $invoices->total() }}</strong> resultados
                </span>
                
                {{-- Botón Exportar Excel --}}
                <a href="{{ route('invoices.export', request()->query()) }}" class="filter-btn-clear" style="text-decoration:none; margin-left:auto; display:inline-flex; align-items:center; gap:0.4rem; border-color:#22c55e; color:#16a34a;" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='none'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Exportar Excel
                </a>

                <a href="{{ route('invoices.create') }}" class="filter-btn-primary" style="text-decoration:none; margin-left:0.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Nueva Factura
                </a>
            </div>
        </form>
    </div>

    {{-- Panel de Exportar Recibos (oculto por defecto) --}}
    <div id="export-payments-panel" class="filter-panel" style="display: none; margin-bottom: 1.5rem; background-color: #f8fafc; border: 1px solid #e2e8f0;">
        <form method="GET" action="{{ route('payments.export') }}" class="filter-panel-form" style="padding: 1rem;">
            <div style="margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Descargar Informe de Pagos / Abonos</div>
            <div class="filter-panel-fields" style="align-items: flex-end;">
                <div class="filter-field">
                    <label class="filter-label">Fecha de Inicio</label>
                    <div class="filter-input-wrap">
                        <input type="date" name="start_date" class="filter-search-input" required style="padding-left: 0.75rem;">
                    </div>
                </div>
                <div class="filter-field">
                    <label class="filter-label">Fecha de Fin</label>
                    <div class="filter-input-wrap">
                        <input type="date" name="end_date" class="filter-search-input" required style="padding-left: 0.75rem;">
                    </div>
                </div>
                <div class="filter-field">
                    <button type="submit" class="filter-btn-primary" style="background-color: #16a34a; border-color: #16a34a; height: 42px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Exportar Informe
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="table-container">
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Nº Factura</th>
                        <th>Cliente</th>
                        @php
                            $sortBy = request()->query('sort_by', 'issue_date');
                            $sortDir = request()->query('sort_dir', 'desc');
                            
                            $getSortUrl = function($column) use ($sortBy, $sortDir) {
                                $newDir = ($sortBy === $column && $sortDir === 'desc') ? 'asc' : 'desc';
                                return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_dir' => $newDir]);
                            };
                            
                            $getSortIcon = function($column) use ($sortBy, $sortDir) {
                                if ($sortBy !== $column) return '<span style="color:#cbd5e1; font-size:0.8rem; margin-left:4px;">↕</span>';
                                return $sortDir === 'asc' 
                                    ? '<span style="color:var(--primary); font-size:0.8rem; margin-left:4px;">↑</span>' 
                                    : '<span style="color:var(--primary); font-size:0.8rem; margin-left:4px;">↓</span>';
                            };
                        @endphp
                        <th>
                            <a href="{{ $getSortUrl('issue_date') }}" style="color:inherit; text-decoration:none; display:flex; align-items:center;">
                                Fecha {!! $getSortIcon('issue_date') !!}
                            </a>
                        </th>
                        <th style="text-align:right;">
                            <a href="{{ $getSortUrl('total_amount') }}" style="color:inherit; text-decoration:none; display:flex; align-items:center; justify-content:flex-end;">
                                Total {!! $getSortIcon('total_amount') !!}
                            </a>
                        </th>
                        <th style="text-align:right;">
                            <a href="{{ $getSortUrl('discount') }}" style="color:inherit; text-decoration:none; display:flex; align-items:center; justify-content:flex-end;">
                                Descuento {!! $getSortIcon('discount') !!}
                            </a>
                        </th>
                        <th style="text-align:right;">
                            <a href="{{ $getSortUrl('total_payments') }}" style="color:inherit; text-decoration:none; display:flex; align-items:center; justify-content:flex-end;">
                                Cobrado {!! $getSortIcon('total_payments') !!}
                            </a>
                        </th>
                        <th style="text-align:right;">
                            <a href="{{ $getSortUrl('current_balance') }}" style="color:inherit; text-decoration:none; display:flex; align-items:center; justify-content:flex-end;">
                                Saldo {!! $getSortIcon('current_balance') !!}
                            </a>
                        </th>
                        <th style="text-align:center;">Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td><strong style="color:var(--primary);">{{ $inv->invoice_number }}</strong></td>
                            <td>
                                <div style="font-weight:600;">{{ $inv->client_name }}</div>
                            </td>
                            <td style="white-space:nowrap;color:var(--text-muted);font-size:0.85rem;">
                                {{ fecha_co($inv->issue_date) }}</td>
                            <td style="text-align:right;">$ {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                            <td style="text-align:right;">
                                {{ $inv->discount > 0 ? '$ ' . number_format($inv->discount, 0, ',', '.') : '—' }}</td>
                            <td style="text-align:right;" class="text-success">$
                                {{ number_format($inv->total_payments, 0, ',', '.') }}</td>
                            <td style="text-align:right;">
                                <strong class="{{ $inv->current_balance > 0 ? 'text-warning' : 'text-success' }}">
                                    $ {{ number_format($inv->current_balance, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td style="text-align:center;">
                                <span class="badge {{ $inv->configured_status }}" style="display:inline-flex; align-items:center; gap:0.35rem; text-align:left;">
                                    @if($inv->configured_status === 'pagada')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    @elseif($inv->configured_status === 'anulada')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    @endif
                                    {{ ucfirst($inv->configured_status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('invoices.show', $inv->invoice_id) }}" class="btn-sm">Ver →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
                                    style="color:var(--text-muted);display:block;margin:0 auto 0.75rem">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                </svg>
                                No se encontraron facturas con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-wrapper" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div style="font-size:0.875rem; color:var(--text-muted);">
            Mostrando <strong>{{ $invoices->firstItem() ?? 0 }}</strong> a <strong>{{ $invoices->lastItem() ?? 0 }}</strong> de <strong>{{ $invoices->total() }}</strong> facturas (Página {{ $invoices->currentPage() }} de {{ $invoices->lastPage() }})
        </div>
        <div>
            {{ $invoices->links('vendor.pagination.simple') }}
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.filter-panel-form');
        if (!form) return;

        // Restaurar foco si se guardó en la sesión
        const lastFocused = sessionStorage.getItem('lastFocusedFilter');
        if (lastFocused) {
            const inputToFocus = form.querySelector(`input[name="${lastFocused}"]`);
            if (inputToFocus) {
                inputToFocus.focus();
                // Mover el cursor al final
                const len = inputToFocus.value.length;
                inputToFocus.setSelectionRange(len, len);
            }
            sessionStorage.removeItem('lastFocusedFilter');
        }

        let timeout = null;
        
        // Auto-submit en campos de texto con retraso (debounce)
        const inputs = form.querySelectorAll('input[type="text"]');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                sessionStorage.setItem('lastFocusedFilter', input.name);
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    form.submit();
                }, 600); // 600ms de espera antes de aplicar el filtro
            });
        });

        // Auto-submit inmediato en los selectores
        const selects = form.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                form.submit();
            });
        });
    });
    </script>
@endsection