@extends('layout')

@section('title', 'Factura ' . $invoice->invoice_number)

@section('content')

{{-- Encabezado --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
        <a href="{{ route('invoices.index') }}" style="font-size:0.85rem; color:var(--text-muted); text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Volver a Cartera
        </a>
        <h2 style="margin-top:0.35rem;font-size:1.75rem;font-weight:700;">Factura <span style="color:var(--primary)">#{{ $invoice->invoice_number }}</span></h2>
        <p style="color:var(--text-muted); margin-top:0.2rem;">Cliente: <strong style="color:var(--text-main)">{{ $invoice->client->name }}</strong>
            @if($invoice->client->document_id) <span style="font-size:0.8rem; margin-left:0.5rem;">· {{ $invoice->client->document_id }}</span>@endif
        </p>
        <p style="color:var(--text-muted); margin-top:0.1rem; font-size:0.85rem;">
            Vendedor: <strong style="color:var(--primary)">{{ $invoice->seller ? $invoice->seller->name : 'Sin asignar' }}</strong>
        </p>
    </div>
    <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
        {{-- Estado actual (solo lectura, lo gestiona el sistema) --}}
        <span class="badge {{ $invoice->status }}" style="font-size:0.85rem;padding:0.35rem 0.9rem;">
            @if($invoice->status === 'pagada')
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg>
            @elseif($invoice->status === 'anulada')
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            @endif
            {{ ucfirst($invoice->status) }}
        </span>

        {{-- Botón Editar factura --}}
        <a href="{{ route('invoices.edit', $invoice) }}" class="filter-btn-clear" style="text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Editar Factura
        </a>

        {{-- Botón Anular / Re-activar --}}
        @if($invoice->status === 'anulada')
        <form method="POST" action="{{ route('invoices.updateStatus', $invoice) }}"
              onsubmit="return confirm('¿Seguro que deseas RE-ACTIVAR la factura #{{ $invoice->invoice_number }}? Volverá al estado correspondiente según sus abonos.')">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="pendiente">
            <button type="submit" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;background:#fff;color:#10b981;border:1px solid #a7f3d0;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='#f0fdf4';this.style.borderColor='#10b981'" onmouseout="this.style.background='#fff';this.style.borderColor='#a7f3d0'">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Re-activar Factura
            </button>
        </form>
        @else
        <form method="POST" action="{{ route('invoices.updateStatus', $invoice) }}"
              onsubmit="return confirm('¿Seguro que deseas ANULAR la factura #{{ $invoice->invoice_number }}? Esta acción cambiará su estado a anulada.')">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="anulada">
            <button type="submit" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;background:#fff;color:#dc2626;border:1px solid #fca5a5;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='#fef2f2';this.style.borderColor='#dc2626'" onmouseout="this.style.background='#fff';this.style.borderColor='#fca5a5'">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Anular Factura
            </button>
        </form>
        @endif

        {{-- Botón Abono --}}
        @if($invoice->status !== 'anulada')
        <button onclick="openModal('modal-abono')" class="filter-btn-primary" style="gap:0.4rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Registrar Abono
        </button>

        {{-- Botón Ajuste --}}
        <button onclick="openModal('modal-ajuste')" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.55rem 1.1rem;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;border-radius:8px;font-size:0.875rem;font-weight:600;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='#ffedd5'" onmouseout="this.style.background='#fff7ed'">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            Ajuste / Devolución
        </button>
        @endif
    </div>
</div>

{{-- KPIs --}}
<div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:2rem;">
    <div class="kpi-card">
        <span class="kpi-title">Total Factura</span>
        <span class="kpi-value" style="font-size:1.5rem;">$ {{ number_format($summary->total_amount, 0, ',', '.') }}</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-title">Descuento</span>
        <span class="kpi-value" style="font-size:1.5rem;">$ {{ number_format($summary->discount, 0, ',', '.') }}</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-title">Total Cobrado</span>
        <span class="kpi-value success" style="font-size:1.5rem;">$ {{ number_format($summary->total_payments, 0, ',', '.') }}</span>
    </div>
    <div class="kpi-card">
        <span class="kpi-title">Saldo Pendiente</span>
        <span class="kpi-value {{ $summary->current_balance > 0 ? 'warning' : 'success' }}" style="font-size:1.5rem;">
            $ {{ number_format($summary->current_balance, 0, ',', '.') }}
        </span>
    </div>
</div>

{{-- Tabla historial --}}
<div class="table-container">
    <div class="table-header">
        <h3>Historial de Transacciones</h3>
        <span style="color:var(--text-muted);font-size:0.875rem;">{{ $invoice->payments->count() + $invoice->adjustments->count() }} registro(s)</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Tipo</th>
                <th>Observación</th>
                <th style="width:50px;"></th>
            </tr>
        </thead>
        <tbody>
            @php
                $transactions = collect();
                foreach($invoice->payments as $p) {
                    $transactions->push((object)['is_payment'=>true,'date'=>$p->payment_date,'amount'=>$p->amount,'label'=>$p->payment_method,'observation'=>$p->observation,'model'=>$p]);
                }
                foreach($invoice->adjustments as $a) {
                    $transactions->push((object)['is_payment'=>false,'date'=>$a->created_at,'amount'=>$a->amount,'label'=>str_replace('_',' ',$a->type),'observation'=>$a->observation,'model'=>$a]);
                }
                $transactions = $transactions->sortByDesc('date');
            @endphp

            @forelse($transactions as $trx)
            <tr>
                <td style="white-space:nowrap;">{{ fecha_co($trx->date) }}</td>
                <td><strong class="{{ $trx->is_payment ? 'text-success' : 'text-warning' }}">$ {{ number_format($trx->amount, 0, ',', '.') }}</strong></td>
                <td>
                    @if($trx->is_payment)
                        <span class="badge pagada" style="text-transform:capitalize;">Abono: {{ $trx->label }}</span>
                    @else
                        <span class="badge anulada" style="text-transform:capitalize; background:rgba(249,115,22,0.1); color:#c2410c; border-color:rgba(249,115,22,0.3);">Ajuste: {{ $trx->label }}</span>
                    @endif
                </td>
                <td style="font-size:0.85rem;color:var(--text-muted);">{{ $trx->observation ?? '—' }}</td>
                <td>
                    @if($trx->is_payment)
                        <form method="POST" action="{{ route('payments.destroy', $trx->model) }}" onsubmit="return confirm('¿Eliminar este abono?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-sm btn-danger" title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('adjustments.destroy', $trx->model) }}" onsubmit="return confirm('¿Eliminar este ajuste?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-sm btn-danger" title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty-state">No hay transacciones registradas. Usa los botones de arriba para agregar abonos o ajustes.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($invoice->observation)
<div class="page-card" style="margin-top:1rem;">
    <p style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">Observación de la factura</p>
    <p>{{ $invoice->observation }}</p>
</div>
@endif


{{-- ═══════════════════════════════════════════ --}}
{{-- MODAL: Registrar Abono                       --}}
{{-- ═══════════════════════════════════════════ --}}
<div id="modal-abono" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title">
                    <svg style="vertical-align:middle;margin-right:6px" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Registrar Abono
                </div>
                <div class="modal-subtitle">Factura #{{ $invoice->invoice_number }} · Saldo: $ {{ number_format($summary->current_balance, 0, ',', '.') }}</div>
            </div>
            <button class="modal-close-btn" onclick="closeModal('modal-abono')" title="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('payments.store', $invoice) }}">
            @csrf
            <div class="form-group" style="margin-bottom:1rem;">
                <label style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);display:block;margin-bottom:0.4rem;">Monto del Abono <span style="color:var(--danger)">*</span></label>
                <div style="position:relative;">
                    <span style="position:absolute;left:0.9rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:600;pointer-events:none;">$</span>
                    <input type="number" name="amount" step="0.01" min="0.01" class="form-input" placeholder="0.00" required style="width:100%;box-sizing:border-box;padding-left:1.75rem;">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div class="form-group">
                    <label style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);display:block;margin-bottom:0.4rem;">Fecha del Pago <span style="color:var(--danger)">*</span></label>
                    <input type="date" name="payment_date" class="form-input" value="{{ date('Y-m-d') }}" required style="width:100%;box-sizing:border-box;">
                </div>
                <div class="form-group">
                    <label style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);display:block;margin-bottom:0.4rem;">Método de Pago</label>
                    <select name="payment_method" class="form-input" style="width:100%;box-sizing:border-box;cursor:pointer;">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="datáfono">Datáfono</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);display:block;margin-bottom:0.4rem;">Observación <span style="font-weight:400;font-size:0.75rem;text-transform:none;">(opcional)</span></label>
                <textarea name="observation" class="form-input" rows="2" placeholder="Notas del pago..." style="width:100%;box-sizing:border-box;resize:none;"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:0.75rem;">
                <button type="button" class="filter-btn-clear" onclick="closeModal('modal-abono')">Cancelar</button>
                <button type="submit" class="filter-btn-primary" style="padding:0.6rem 1.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Guardar Abono
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ═══════════════════════════════════════════ --}}
{{-- MODAL: Registrar Ajuste / Devolución         --}}
{{-- ═══════════════════════════════════════════ --}}
<div id="modal-ajuste" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title">
                    <svg style="vertical-align:middle;margin-right:6px" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c2410c" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    Ajuste / Devolución
                </div>
                <div class="modal-subtitle">Reduce el saldo por devoluciones, notas crédito o descuentos extra.</div>
            </div>
            <button class="modal-close-btn" onclick="closeModal('modal-ajuste')" title="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('adjustments.store', $invoice) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div class="form-group">
                    <label style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);display:block;margin-bottom:0.4rem;">Monto a Descontar <span style="color:var(--danger)">*</span></label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:0.9rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:600;pointer-events:none;">$</span>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-input" placeholder="0.00" required style="width:100%;box-sizing:border-box;padding-left:1.75rem;">
                    </div>
                </div>
                <div class="form-group">
                    <label style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);display:block;margin-bottom:0.4rem;">Tipo de Ajuste <span style="color:var(--danger)">*</span></label>
                    <select name="type" class="form-input" style="width:100%;box-sizing:border-box;cursor:pointer;">
                        <option value="devolucion">Devolución de mercancía</option>
                        <option value="descuento_adicional">Descuento adicional</option>
                        <option value="nota_credito">Nota crédito</option>
                        <option value="anulacion">Anulación parcial</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);display:block;margin-bottom:0.4rem;">Observación <span style="color:var(--danger)">*</span></label>
                <textarea name="observation" class="form-input" rows="2" placeholder="Motivo del ajuste o devolución..." required style="width:100%;box-sizing:border-box;resize:none;"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:0.75rem;">
                <button type="button" class="filter-btn-clear" onclick="closeModal('modal-ajuste')">Cancelar</button>
                <button type="submit" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.6rem 1.5rem;background:#c2410c;color:#fff;border:none;border-radius:8px;font-size:0.875rem;font-weight:600;cursor:pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Guardar Ajuste
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Abrir modal si hay errores de validación --}}
@if($errors->hasAny(['amount', 'payment_date', 'payment_method']))
<script>document.addEventListener('DOMContentLoaded',()=>openModal('modal-abono'));</script>
@endif
@if($errors->has('type') || ($errors->has('observation') && !$errors->hasAny(['payment_date','payment_method'])))
<script>document.addEventListener('DOMContentLoaded',()=>openModal('modal-ajuste'));</script>
@endif

@endsection
