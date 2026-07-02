<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Seller;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        // Por defecto, muestra del 1 al 15 o del 16 al fin de mes actual
        $today = Carbon::today();
        $defaultStart = $today->day <= 15 ? $today->copy()->startOfMonth() : $today->copy()->day(16);
        $defaultEnd = $today->day <= 15 ? $today->copy()->day(15) : $today->copy()->endOfMonth();

        $startDate = $request->input('start_date', $defaultStart->format('Y-m-d'));
        $endDate = $request->input('end_date', $defaultEnd->format('Y-m-d'));
        $sellerId = $request->input('seller_id', '');

        // Traer todos los vendedores para el filtro
        $sellers = Seller::orderBy('name')->get();

        // Consulta de abonos en ese rango
        $query = Payment::with(['invoice.client', 'invoice.seller'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->whereHas('invoice', function ($q) {
                // Solo facturas que tengan vendedor asignado
                $q->whereNotNull('seller_id');
            });

        if (!empty($sellerId)) {
            $query->whereHas('invoice', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            });
        }

        $payments = $query->orderBy('payment_date', 'asc')->get();

        // Procesar pagos y calcular días y comisión
        $processedPayments = collect();
        $summaryBySeller = [];

        foreach ($payments as $payment) {
            $invoice = $payment->invoice;
            $seller = $invoice->seller;
            if (!$seller) continue;

            $issueDate = Carbon::parse($invoice->issue_date);
            $paymentDate = Carbon::parse($payment->payment_date);

            // "Los 60 días empiezan del día siguiente" -> Usamos diffInDays, que calcula días exactos.
            // Ej: Si la factura es del 1 y el abono el 2, diffInDays es 1.
            $daysOld = $issueDate->diffInDays($paymentDate, false);

            $commissionable = ($daysOld >= 0 && $daysOld <= 60);

            $processedPayments->push((object)[
                'id' => $payment->id,
                'seller_name' => $seller->name,
                'client_name' => $invoice->client->name,
                'invoice_number' => $invoice->invoice_number,
                'issue_date' => $invoice->issue_date,
                'payment_date' => $payment->payment_date,
                'amount' => $payment->amount,
                'days_old' => max(0, $daysOld),
                'commissionable' => $commissionable
            ]);

            // Resumen por vendedor
            if (!isset($summaryBySeller[$seller->id])) {
                $summaryBySeller[$seller->id] = (object)[
                    'name' => $seller->name,
                    'total_collected' => 0,
                    'commissionable_amount' => 0,
                    'non_commissionable_amount' => 0
                ];
            }

            $summaryBySeller[$seller->id]->total_collected += $payment->amount;
            if ($commissionable) {
                $summaryBySeller[$seller->id]->commissionable_amount += $payment->amount;
            } else {
                $summaryBySeller[$seller->id]->non_commissionable_amount += $payment->amount;
            }
        }

        // Si se solicitó exportar a Excel
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportExcel($processedPayments, $startDate, $endDate);
        }

        return view('commissions.index', compact('processedPayments', 'summaryBySeller', 'sellers', 'startDate', 'endDate', 'sellerId'));
    }

    private function exportExcel($payments, $startDate, $endDate)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Liquidación Comisiones');

        // Título general
        $sheet->setCellValue('A1', 'Reporte de Liquidación de Comisiones');
        $sheet->setCellValue('A2', "Período: " . fecha_co($startDate) . " a " . fecha_co($endDate));
        $sheet->getStyle('A1:A2')->getFont()->setBold(true);

        // Encabezados
        $headers = ['Vendedor', 'Factura', 'Cliente', 'Fecha Emisión', 'Fecha Abono', 'Días Transcurridos', 'Valor Abono', 'Aplica Comisión'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $sheet->getStyle('A4:H4')->getFont()->setBold(true);
        $sheet->getStyle('A4:H4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3F4F6');

        $row = 5;
        foreach ($payments as $p) {
            $sheet->setCellValue('A' . $row, $p->seller_name);
            $sheet->setCellValue('B' . $row, $p->invoice_number);
            $sheet->setCellValue('C' . $row, $p->client_name);
            $sheet->setCellValue('D' . $row, $p->issue_date);
            $sheet->setCellValue('E' . $row, $p->payment_date);
            $sheet->setCellValue('F' . $row, $p->days_old);
            $sheet->setCellValue('G' . $row, $p->amount);
            $sheet->setCellValue('H' . $row, $p->commissionable ? 'SÍ' : 'NO');
            
            if (!$p->commissionable) {
                $sheet->getStyle('H' . $row)->getFont()->getColor()->setARGB('FFDC2626'); // Rojo
            } else {
                $sheet->getStyle('H' . $row)->getFont()->getColor()->setARGB('FF10B981'); // Verde
            }
            
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Auto filter
        $sheet->setAutoFilter('A4:H4');

        $fileName = "Liquidacion_Comisiones_{$startDate}_al_{$endDate}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
