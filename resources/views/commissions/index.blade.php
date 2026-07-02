@extends('layout')

@section('title', 'Liquidación de Comisiones')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2 style="font-size:1.6rem; font-weight:700; color:var(--text-main);">Liquidación de Comisiones</h2>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.25rem;">Regla: Solo aplican abonos realizados dentro de los primeros 60 días tras la emisión de la factura.</p>
    </div>
</div>

{{-- Filtros --}}
<div class="page-card" style="padding:1.5rem; margin-bottom:2rem;">
    <form method="GET" action="{{ route('commissions.index') }}" style="display:flex; gap:1.5rem; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="flex:1; min-width:150px; margin:0;">
            <label class="form-label">Fecha Inicio (Abonos)</label>
            <input type="date" name="start_date" class="form-input" value="{{ $startDate }}" style="width:100%; box-sizing:border-box;">
        </div>
        <div class="form-group" style="flex:1; min-width:150px; margin:0;">
            <label class="form-label">Fecha Fin (Abonos)</label>
            <input type="date" name="end_date" class="form-input" value="{{ $endDate }}" style="width:100%; box-sizing:border-box;">
        </div>
        <div class="form-group" style="flex:1.5; min-width:200px; margin:0;">
            <label class="form-label">Filtrar por Vendedor</label>
            <select name="seller_id" class="form-input" style="width:100%; box-sizing:border-box;">
                <option value="">— Todos los Vendedores —</option>
                @foreach($sellers as $seller)
                    <option value="{{ $seller->id }}" {{ $sellerId == $seller->id ? 'selected' : '' }}>
                        {{ $seller->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; gap:0.75rem;">
            <button type="submit" class="filter-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Generar Liquidación
            </button>
            <button type="submit" name="export" value="excel" class="filter-btn-clear" style="border-color:#22c55e; color:#16a34a;" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='none'">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Exportar Excel
            </button>
        </div>
    </form>
</div>

{{-- Resumen Consolidado por Vendedor --}}
@if(count($summaryBySeller) > 0)
<div style="margin-bottom:2rem;">
    <h3 style="font-size:1.1rem; font-weight:700; color:var(--text-main); margin-bottom:1rem;">Resumen Consolidado</h3>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1rem;">
        @foreach($summaryBySeller as $summary)
        <div class="kpi-card" style="display:flex; flex-direction:column; gap:0.5rem; padding:1.25rem;">
            <div style="font-size:1rem; font-weight:700; color:var(--text-main);">{{ $summary->name }}</div>
            <div style="display:flex; justify-content:space-between; font-size:0.85rem; color:var(--text-muted);">
                <span>Recaudo Total:</span>
                <span style="font-weight:600; color:var(--text-main);">$ {{ number_format($summary->total_collected, 0, ',', '.') }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.85rem; color:var(--text-muted);">
                <span>Recaudo <strong style="color:#ef4444;">NO comisionable</strong> (> 60 días):</span>
                <span style="font-weight:600; color:#ef4444;">$ {{ number_format($summary->non_commissionable_amount, 0, ',', '.') }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.95rem; margin-top:0.5rem; padding-top:0.5rem; border-top:1px dashed var(--surface-border);">
                <span style="font-weight:700; color:#10b981;">Base Comisionable (≤ 60 días):</span>
                <span style="font-weight:800; color:#10b981;">$ {{ number_format($summary->commissionable_amount, 0, ',', '.') }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Detalles de Abonos --}}
<div class="page-card">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Vendedor</th>
                    <th>Nº Factura</th>
                    <th>Fecha Emisión</th>
                    <th>Fecha Abono</th>
                    <th style="text-align:right;">Días</th>
                    <th style="text-align:right;">Valor Abono</th>
                    <th style="text-align:center;">¿Aplica Comisión?</th>
                </tr>
            </thead>
            <tbody>
                @forelse($processedPayments as $payment)
                <tr>
                    <td><strong style="color:var(--text-main);">{{ $payment->seller_name }}</strong></td>
                    <td>
                        <strong style="color:var(--primary);">{{ $payment->invoice_number }}</strong>
                        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.15rem;">{{ $payment->client_name }}</div>
                    </td>
                    <td style="color:var(--text-muted); font-size:0.85rem;">{{ fecha_co($payment->issue_date) }}</td>
                    <td style="color:var(--text-main); font-weight:600; font-size:0.85rem;">{{ fecha_co($payment->payment_date) }}</td>
                    <td style="text-align:right;">
                        @if($payment->days_old <= 60)
                            <span style="color:#10b981; font-weight:700;">{{ $payment->days_old }}</span>
                        @else
                            <span style="color:#ef4444; font-weight:700;">{{ $payment->days_old }}</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <strong style="color:var(--text-main);">$ {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                    </td>
                    <td style="text-align:center;">
                        @if($payment->commissionable)
                            <span class="badge pagada" style="display:inline-flex; align-items:center; gap:0.35rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Sí aplica
                            </span>
                        @else
                            <span class="badge anulada" style="display:inline-flex; align-items:center; gap:0.35rem;" title="El abono superó los 60 días desde la emisión de la factura">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                No (Vencido)
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted); display:block; margin:0 auto 0.75rem;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        No se encontraron abonos de facturas con vendedor asignado en este rango de fechas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
