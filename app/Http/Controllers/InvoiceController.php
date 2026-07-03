<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->query('search');
        $status  = $request->query('status');
        $cliente = $request->query('cliente');
        
        $sortBy  = $request->query('sort_by', 'issue_date');
        $sortDir = $request->query('sort_dir', 'asc');
        
        $allowedSorts = [
            'issue_date' => 'issue_date',
            'total_amount' => 'total_amount',
            'discount' => 'discount',
            'total_payments' => 'total_payments',
            'current_balance' => 'current_balance'
        ];
        
        $sortColumn = $allowedSorts[$sortBy] ?? 'issue_date';
        $validSortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? $sortDir : 'asc';

        $invoices = DB::table('v_invoices_summary')
            ->when($search,  fn($q) => $q->where('invoice_number', 'like', "%{$search}%"))
            ->when($status,  fn($q) => $q->where('configured_status', $status))
            ->when($cliente, fn($q) => $q->where('client_name', 'like', "%{$cliente}%"))
            ->orderBy($sortColumn, $validSortDir)
            ->when($sortColumn !== 'invoice_id', fn($q) => $q->orderByDesc('invoice_id'))
            ->paginate(25)
            ->withQueryString();

        $totals = DB::selectOne("
            SELECT
                COUNT(*) as total_facturas,
                SUM(current_balance) as total_pendiente,
                SUM(CASE WHEN configured_status = 'pendiente' THEN 1 ELSE 0 END) as facturas_pendientes
            FROM v_invoices_summary
        ");

        return view('invoices.index', compact('invoices', 'search', 'status', 'cliente', 'totals'));
    }

    public function exportExcel(Request $request)
    {
        $search  = $request->query('search');
        $status  = $request->query('status');
        $cliente = $request->query('cliente');
        
        $sortBy  = $request->query('sort_by', 'issue_date');
        $sortDir = $request->query('sort_dir', 'asc');
        
        $allowedSorts = [
            'issue_date' => 'issue_date',
            'total_amount' => 'total_amount',
            'discount' => 'discount',
            'total_payments' => 'total_payments',
            'current_balance' => 'current_balance'
        ];
        
        $sortColumn = $allowedSorts[$sortBy] ?? 'issue_date';
        $validSortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? $sortDir : 'asc';

        $invoices = DB::table('v_invoices_summary')
            ->when($search,  fn($q) => $q->where('invoice_number', 'like', "%{$search}%"))
            ->when($status,  fn($q) => $q->where('configured_status', $status))
            ->when($cliente, fn($q) => $q->where('client_name', 'like', "%{$cliente}%"))
            ->orderBy($sortColumn, $validSortDir)
            ->when($sortColumn !== 'invoice_id', fn($q) => $q->orderByDesc('invoice_id'))
            ->get();
            
        // Load payments for the retrieved invoices
        $invoiceIds = $invoices->pluck('invoice_id')->toArray();
        $payments = DB::table('payments')
            ->whereIn('invoice_id', $invoiceIds)
            ->orderBy('payment_date', 'asc')
            ->get()
            ->groupBy('invoice_id');
            
        $maxPayments = 0;
        foreach ($payments as $invPayments) {
            if (count($invPayments) > $maxPayments) {
                $maxPayments = count($invPayments);
            }
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Estilos básicos
        $sheet->setTitle('Cartera Facturas');
        
        // Encabezados
        $headers = ['Número Factura', 'Cliente', 'Fecha Emisión', 'Valor Total', 'Descuento', 'Valor Neto', 'Total Cobrado', 'Saldo Pendiente', 'Estado'];
        
        for ($i = 1; $i <= $maxPayments; $i++) {
            $headers[] = "Fecha Abono $i";
            $headers[] = "Valor Abono $i";
        }
        
        foreach ($headers as $colIdx => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue($colLetter . '1', $header);
            $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);
            $sheet->getStyle($colLetter . '1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F1F5F9');
        }

        // Datos
        $row = 2;
        foreach ($invoices as $inv) {
            $sheet->setCellValue('A' . $row, $inv->invoice_number);
            $sheet->setCellValue('B' . $row, $inv->client_name);
            $sheet->setCellValue('C' . $row, fecha_co($inv->issue_date));
            $sheet->setCellValue('D' . $row, $inv->total_amount);
            $sheet->setCellValue('E' . $row, $inv->discount);
            $sheet->setCellValue('F' . $row, $inv->net_amount);
            $sheet->setCellValue('G' . $row, $inv->total_payments + $inv->total_adjustments);
            $sheet->setCellValue('H' . $row, $inv->current_balance);
            $sheet->setCellValue('I' . $row, ucfirst($inv->configured_status));

            // Formatos numéricos de moneda colombiana
            $sheet->getStyle('D' . $row . ':H' . $row)->getNumberFormat()->setFormatCode('$#,##0');
            
            $invPayments = $payments->get($inv->invoice_id) ?? [];
            $colIndex = 10; // Empezar después de Estado (I es la 9)
            
            foreach ($invPayments as $payment) {
                $dateCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $amountCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                
                $sheet->setCellValue($dateCol . $row, fecha_co($payment->payment_date));
                $sheet->setCellValue($amountCol . $row, $payment->amount);
                $sheet->getStyle($amountCol . $row)->getNumberFormat()->setFormatCode('$#,##0');
                
                $colIndex += 2;
            }
            
            $row++;
        }

        // Autoajustar columnas
        foreach (range(1, count($headers)) as $colIdx) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Cartera_Surtitornillos_' . date('d_m_Y') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('invoices.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'invoice_number' => 'required|string|max:50|unique:invoices,invoice_number',
            'issue_date'     => 'required|date',
            'total_amount'   => 'required|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0',
            'observation'    => 'nullable|string|max:1000',
        ]);

        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['status'] = 'pendiente';

        $invoice = Invoice::create($validated);

        \App\Helpers\AuditHelper::log(
            'creacion_factura',
            'Invoice',
            $invoice->id,
            "Creó la factura #{$invoice->invoice_number} para el cliente \"{$invoice->client->name}\" por valor neto de $ " . number_format($invoice->total_amount - $invoice->discount, 0, ',', '.')
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Factura #{$invoice->invoice_number} creada exitosamente.");
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'payments', 'adjustments']);

        $summary = DB::selectOne("
            SELECT * FROM v_invoices_summary WHERE invoice_id = ?
        ", [$invoice->id]);

        return view('invoices.show', compact('invoice', 'summary'));
    }

    public function edit(Invoice $invoice)
    {
        $clients = Client::orderBy('name')->get();
        return view('invoices.edit', compact('invoice', 'clients'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'invoice_number' => 'required|string|max:50|unique:invoices,invoice_number,' . $invoice->id,
            'issue_date'     => 'required|date',
            'total_amount'   => 'required|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0',
            'observation'    => 'nullable|string|max:1000',
        ]);

        $validated['discount'] = $validated['discount'] ?? 0;

        $invoice->update($validated);

        \App\Helpers\AuditHelper::log(
            'edicion_factura',
            'Invoice',
            $invoice->id,
            "Editó la factura #{$invoice->invoice_number}"
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Factura #{$invoice->invoice_number} actualizada.");
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'required|in:pendiente,pagada,anulada',
        ]);

        $oldStatus = $invoice->status;
        $targetStatus = $validated['status'];

        if ($targetStatus !== 'anulada') {
            // Recalcular saldo real
            $totalPagado = $invoice->payments()->sum('amount');
            $totalAjuste = $invoice->adjustments()->sum('amount');
            $saldo = $invoice->total_amount - $invoice->discount - $totalPagado - $totalAjuste;
            
            $targetStatus = ($saldo <= 0.01) ? 'pagada' : 'pendiente';
        }

        $invoice->update(['status' => $targetStatus]);

        \App\Helpers\AuditHelper::log(
            'anulacion_factura',
            'Invoice',
            $invoice->id,
            $targetStatus === 'anulada' 
                ? "Anuló la factura #{$invoice->invoice_number}" 
                : "Re-activó la factura #{$invoice->invoice_number} (Estado asignado: \"{$targetStatus}\")"
        );

        return back()->with('success', $targetStatus === 'anulada' ? 'Factura anulada con éxito.' : 'Factura re-activada con éxito.');
    }
}
