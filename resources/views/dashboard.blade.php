@extends('layout')

@section('title', 'Dashboard')

@section('content')

{{-- Resumen KPIs --}}
<div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom:1.5rem;">
    <div class="kpi-card">
        <div class="kpi-icon kpi-icon--neutral">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <span class="kpi-title">Total Facturas</span>
        <span class="kpi-value">{{ number_format($kpis->total_facturas) }}</span>
    </div>
    <div class="kpi-card kpi-card--warning">
        <div class="kpi-icon kpi-icon--warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <span class="kpi-title">Facturas Pendientes</span>
        <span class="kpi-value warning">{{ number_format($kpis->facturas_pendientes) }}</span>
    </div>
    <div class="kpi-card kpi-card--warning">
        <div class="kpi-icon kpi-icon--warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <span class="kpi-title">Cartera Pendiente</span>
        <span class="kpi-value warning">$ {{ number_format($kpis->total_pendiente, 0, ',', '.') }}</span>
    </div>
</div>

    {{-- Fila 2: Tabla de actividad + Top Deudores --}}
    <div class="dash-grid-2">

        {{-- Facturas más Antiguas pendientes --}}
        <div class="table-container">
            <div class="table-header">
                <div>
                    <h3 style="display:flex;align-items:center;gap:0.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Facturas Pendientes más Antiguas
                    </h3>
                    <p style="color:var(--text-muted);font-size:0.8rem;margin-top:2px;">Las que llevan más tiempo sin pagar
                    </p>
                </div>
                <a href="{{ route('invoices.index', ['status' => 'pendiente']) }}" class="btn-sm">Ver todas</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturasAntiguas as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $inv->invoice_id) }}" class="link-primary">
                                    #{{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td>{{ $inv->client_name }}</td>
                            <td style="white-space:nowrap;color:var(--danger);font-size:0.85rem;">{{ fecha_co($inv->issue_date) }}</td>
                            <td><strong class="text-warning">${{ number_format($inv->current_balance, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">¡Sin facturas vencidas!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Top Deudores --}}
        <div class="table-container">
            <div class="table-header">
                <div>
                    <h3 style="display:flex;align-items:center;gap:0.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                        Top Deudores
                    </h3>
                    <p style="color:var(--text-muted);font-size:0.8rem;margin-top:2px;">Clientes con mayor saldo pendiente
                    </p>
                </div>
                <a href="{{ route('clients.index') }}" class="btn-sm">Ver clientes</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Facturas</th>
                        <th>Saldo Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topDeudores as $i => $d)
                        <tr>
                            <td style="font-weight:700;color:{{ $i === 0 ? '#d97706' : 'var(--text-muted)' }}">{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route('invoices.index', ['cliente' => $d->client_name]) }}" class="link-primary">
                                    {{ $d->client_name }}
                                </a>
                            </td>
                            <td style="color:var(--text-muted);">{{ $d->num_facturas }}</td>
                            <td><strong class="text-warning">${{ number_format($d->saldo_total, 0, ',', '.') }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">Sin deudores pendientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Actividad reciente --}}
    <div class="table-container" style="margin-top:1.5rem;">
        <div class="table-header">
            <div>
                <h3>Actividad Reciente</h3>
                <p style="color:var(--text-muted);font-size:0.8rem;margin-top:2px;">Últimas facturas registradas</p>
            </div>
            <a href="{{ route('invoices.index') }}" class="btn-sm">Ver todas</a>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Nº Factura</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Neto</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentInvoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $inv->invoice_id) }}" class="link-primary">
                                    #{{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td>{{ $inv->client_name }}</td>
                            <td style="white-space:nowrap;color:var(--text-muted);font-size:0.85rem;">
                                {{ fecha_co($inv->issue_date) }}</td>
                            <td>${{ number_format($inv->net_amount, 0, ',', '.') }}</td>
                            <td><strong class="{{ $inv->current_balance > 0 ? 'text-warning' : 'text-success' }}">
                                    ${{ number_format($inv->current_balance, 0, ',', '.') }}
                                </strong></td>
                            <td>
                                <span class="badge {{ $inv->configured_status }}" style="display:inline-flex; align-items:center; gap:0.35rem;">
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">Sin facturas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection