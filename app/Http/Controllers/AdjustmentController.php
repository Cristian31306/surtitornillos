<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Adjustment;
use Illuminate\Http\Request;

class AdjustmentController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'type'        => 'required|in:devolucion,descuento_adicional,nota_credito,anulacion',
            'observation' => 'required|string|max:500',
        ]);

        $adjustment = $invoice->adjustments()->create($validated);

        \App\Helpers\AuditHelper::log(
            'ajuste_registrado',
            'Adjustment',
            $adjustment->id,
            "Registró un ajuste de tipo \"" . str_replace('_', ' ', $adjustment->type) . "\" por $ " . number_format($adjustment->amount, 0, ',', '.') . " a la factura #{$invoice->invoice_number}. Razón: {$adjustment->observation}"
        );

        // Recalcular saldo para actualizar estado de la factura si llega a 0
        $totalPagado = $invoice->payments()->sum('amount');
        $totalAjuste = $invoice->adjustments()->sum('amount');
        $saldo = $invoice->total_amount - $invoice->discount - $totalPagado - $totalAjuste;

        if ($saldo <= 0.01 && $invoice->status === 'pendiente') {
            $invoice->update(['status' => 'pagada']);
        }

        return back()->with('success', 'Ajuste registrado exitosamente.');
    }

    public function destroy(Adjustment $adjustment)
    {
        $invoiceId = $adjustment->invoice_id;
        $invoice = Invoice::find($invoiceId);

        \App\Helpers\AuditHelper::log(
            'ajuste_eliminado',
            'Adjustment',
            $adjustment->id,
            "Eliminó ajuste de tipo \"" . str_replace('_', ' ', $adjustment->type) . "\" de $ " . number_format($adjustment->amount, 0, ',', '.') . " de la factura #" . ($invoice ? $invoice->invoice_number : $invoiceId)
        );

        $adjustment->delete();

        // Recalcular y actualizar status a pendiente si hay saldo
        if ($invoice) {
            $totalPagado = $invoice->payments()->sum('amount');
            $totalAjuste = $invoice->adjustments()->sum('amount');
            $saldo = $invoice->total_amount - $invoice->discount - $totalPagado - $totalAjuste;

            if ($saldo > 0.01 && $invoice->status === 'pagada') {
                $invoice->update(['status' => 'pendiente']);
            }
        }

        return back()->with('success', 'Ajuste eliminado.');
    }
}
