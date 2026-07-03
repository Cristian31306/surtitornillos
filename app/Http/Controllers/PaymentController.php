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

    public function exportExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $payments = \Illuminate\Support\Facades\DB::table('payments')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->join('clients', 'invoices.client_id', '=', 'clients.id')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->select('payments.*', 'invoices.invoice_number', 'clients.name as client_name')
            ->orderBy('payment_date', 'asc')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Recibos de Caja');
        
        $headers = ['Recibo ID', 'Fecha Abono', 'Factura', 'Cliente', 'Método de Pago', 'Valor', 'Observaciones'];
        
        foreach ($headers as $colIdx => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue($colLetter . '1', $header);
            $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);
            $sheet->getStyle($colLetter . '1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F1F5F9');
        }

        $row = 2;
        $totalValor = 0;
        foreach ($payments as $payment) {
            $sheet->setCellValue('A' . $row, $payment->id);
            $sheet->setCellValue('B' . $row, fecha_co($payment->payment_date));
            $sheet->setCellValue('C' . $row, $payment->invoice_number);
            $sheet->setCellValue('D' . $row, $payment->client_name);
            $sheet->setCellValue('E' . $row, ucfirst($payment->payment_method));
            $sheet->setCellValue('F' . $row, $payment->amount);
            $sheet->setCellValue('G' . $row, $payment->observation);

            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('$#,##0');
            $totalValor += $payment->amount;
            $row++;
        }

        $sheet->setCellValue('E' . $row, 'TOTAL');
        $sheet->getStyle('E' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('F' . $row, $totalValor);
        $sheet->getStyle('F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('$#,##0');

        foreach (range(1, count($headers)) as $colIdx) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Recibos_de_Caja_' . $startDate . '_al_' . $endDate . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
