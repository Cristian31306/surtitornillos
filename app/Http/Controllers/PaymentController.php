<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:efectivo,transferencia,datáfono,otro',
            'observation'    => 'nullable|string|max:500',
        ]);

        $payment = $invoice->payments()->create($validated);

        \App\Helpers\AuditHelper::log(
            'abono_registrado',
            'Payment',
            $payment->id,
            "Registró abono de $ " . number_format($payment->amount, 0, ',', '.') . " a la factura #{$invoice->invoice_number} vía {$payment->payment_method}"
        );

        // Recalcular saldo y actualizar status si queda en cero
        $totalPagado = $invoice->payments()->sum('amount');
        $totalAjuste = $invoice->adjustments()->sum('amount');
        $saldo = $invoice->total_amount - $invoice->discount - $totalPagado - $totalAjuste;

        if ($saldo <= 0.01) {
            $invoice->update(['status' => 'pagada']);
        }

        return back()->with('success', 'Abono registrado exitosamente.');
    }

    public function destroy(Payment $payment)
    {
        $invoiceId = $payment->invoice_id;
        $invoice = Invoice::find($invoiceId);

        \App\Helpers\AuditHelper::log(
            'abono_eliminado',
            'Payment',
            $payment->id,
            "Eliminó el abono de $ " . number_format($payment->amount, 0, ',', '.') . " de la factura #" . ($invoice ? $invoice->invoice_number : $invoiceId)
        );

        $payment->delete();

        // Recalcular y actualizar status a pendiente si hay saldo
        if ($invoice) {
            $totalPagado = $invoice->payments()->sum('amount');
            $totalAjuste = $invoice->adjustments()->sum('amount');
            $saldo = $invoice->total_amount - $invoice->discount - $totalPagado - $totalAjuste;

            if ($saldo > 0.01 && $invoice->status === 'pagada') {
                $invoice->update(['status' => 'pendiente']);
            }
        }

        return back()->with('success', 'Abono eliminado.');
    }
}
