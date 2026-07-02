@extends('layout')

@section('title', 'Editar Factura ' . $invoice->invoice_number)

@section('content')

<div style="max-width:680px; margin:0 auto;">
    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('invoices.show', $invoice) }}" style="font-size:0.85rem; color:var(--text-muted); text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Volver a la Factura
        </a>
        <h1 style="font-size:1.6rem; font-weight:700; color:var(--text-main); margin-top:0.5rem;">
            Editar Factura <span style="color:var(--primary)">#{{ $invoice->invoice_number }}</span>
        </h1>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.25rem;">Modifica los datos de esta factura.</p>
    </div>

    <div class="page-card" style="padding:2rem;">
        <form method="POST" action="{{ route('invoices.update', $invoice) }}">
            @csrf @method('PUT')

            {{-- Cliente y Vendedor --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div class="form-group">
                    <label style="font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.5rem;">
                        Cliente <span style="color:var(--danger)">*</span>
                    </label>
                    <select name="client_id" class="form-input searchable-select" required style="width:100%; box-sizing:border-box; cursor:pointer;">
                        <option value="">— Seleccione un cliente —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ (old('client_id', $invoice->client_id) == $client->id) ? 'selected' : '' }}>
                                {{ $client->name }}@if($client->document_id) — {{ $client->document_id }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.5rem;">
                        Vendedor
                    </label>
                    <select name="seller_id" class="form-input searchable-select" style="width:100%; box-sizing:border-box; cursor:pointer;">
                        <option value="">— Sin vendedor asignado —</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ old('seller_id', $invoice->seller_id) == $seller->id ? 'selected' : '' }}>
                                {{ $seller->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Número y fecha --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div class="form-group">
                    <label style="font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.5rem;">
                        N° de Factura <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="text" name="invoice_number" class="form-input"
                        value="{{ old('invoice_number', $invoice->invoice_number) }}" required
                        style="width:100%; box-sizing:border-box;">
                </div>
                <div class="form-group">
                    <label style="font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.5rem;">
                        Fecha de Emisión <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="date" name="issue_date" class="form-input"
                        value="{{ old('issue_date', \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m-d')) }}" required
                        style="width:100%; box-sizing:border-box;">
                </div>
            </div>

            {{-- Monto y descuento --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div class="form-group">
                    <label style="font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.5rem;">
                        Monto Total <span style="color:var(--danger)">*</span>
                    </label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:0.9rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-weight:600; pointer-events:none;">$</span>
                        <input type="number" name="total_amount" step="0.01" min="0" class="form-input"
                            value="{{ old('total_amount', $invoice->total_amount) }}" required
                            style="width:100%; box-sizing:border-box; padding-left:1.75rem;">
                    </div>
                </div>
                <div class="form-group">
                    <label style="font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.5rem;">
                        Descuento Inicial
                    </label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:0.9rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-weight:600; pointer-events:none;">$</span>
                        <input type="number" name="discount" step="0.01" min="0" class="form-input"
                            value="{{ old('discount', $invoice->discount) }}"
                            style="width:100%; box-sizing:border-box; padding-left:1.75rem;">
                    </div>
                </div>
            </div>

            {{-- Observación --}}
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label style="font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); display:block; margin-bottom:0.5rem;">
                    Observación <span style="font-weight:400; text-transform:none; font-size:0.78rem;">(opcional)</span>
                </label>
                <textarea name="observation" class="form-input" rows="3"
                    placeholder="Detalles de la factura..."
                    style="width:100%; box-sizing:border-box; resize:vertical;">{{ old('observation', $invoice->observation) }}</textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem; padding-top:1.25rem; border-top:1px solid var(--surface-border);">
                <a href="{{ route('invoices.show', $invoice) }}" class="filter-btn-clear" style="text-decoration:none;">
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
