<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ImportExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa la base de datos de cartera desde el archivo Excel BD (1).xlsx';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = base_path('BD (1).xlsx');

        if (!file_exists($filePath)) {
            $this->error("El archivo no se encuentra en la ruta: {$filePath}");
            return;
        }

        $this->info("Iniciando importación desde Excel...");
        $this->info("Creando usuario administrador por defecto...");

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
            ]
        );
        $this->info("Usuario admin (username: admin / password: admin123) creado exitosamente.");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getSheetByName('Cartera');
            
            if (!$sheet) {
                $this->error("No se encontró la hoja 'Cartera' en el Excel.");
                return;
            }

            $highestRow = $sheet->getHighestRow();
            $this->output->progressStart($highestRow - 1);
            
            DB::beginTransaction();

            $successCount = 0;
            $validationErrors = 0;

            // Mapeo de columnas:
            // A: N Factura | B: Fecha | C: Cliente | D: Total | E: Descuento
            // F-H: Abono1 | I-K: Abono2 | L-N: Abono3 | O-Q: Abono4 | R-T: Abono5
            // U: Saldo
            $paymentCols = [
                ['amt' => 'F', 'date' => 'G', 'method' => 'H'],
                ['amt' => 'I', 'date' => 'J', 'method' => 'K'],
                ['amt' => 'L', 'date' => 'M', 'method' => 'N'],
                ['amt' => 'O', 'date' => 'P', 'method' => 'Q'],
                ['amt' => 'R', 'date' => 'S', 'method' => 'T'],
            ];

            for ($row = 2; $row <= $highestRow; $row++) {
                $invoiceNum = trim((string)$sheet->getCell('A' . $row)->getCalculatedValue());
                if (empty($invoiceNum)) continue;

                $rawDate = $sheet->getCell('B' . $row)->getCalculatedValue();
                $invoiceDate = $this->parseExcelDate($rawDate);
                
                $clientName = trim((string)$sheet->getCell('C' . $row)->getCalculatedValue());
                $clientName = preg_replace('/\s+/', ' ', strtoupper($clientName)); // Normalizar espacios y mayúsculas

                $totalAmount = (float)$sheet->getCell('D' . $row)->getCalculatedValue();
                $discount = (float)$sheet->getCell('E' . $row)->getCalculatedValue();
                $saldoExcel = (float)$sheet->getCell('U' . $row)->getCalculatedValue();

                $client = Client::firstOrCreate(['name' => $clientName]);

                $status = ($saldoExcel <= 0) ? 'pagada' : 'pendiente';

                // Si la factura no tiene fecha, tomamos la fecha del primer abono
                if (!$invoiceDate) {
                    foreach ($paymentCols as $p) {
                        $pDateVal = $sheet->getCell($p['date'] . $row)->getCalculatedValue();
                        $pDate = $this->parseExcelDate($pDateVal);
                        if ($pDate) {
                            $invoiceDate = $pDate;
                            break;
                        }
                    }
                }

                $invoice = Invoice::create([
                    'client_id' => $client->id,
                    'invoice_number' => $invoiceNum,
                    'issue_date' => $invoiceDate,
                    'total_amount' => $totalAmount,
                    'discount' => $discount,
                    'status' => $status,
                ]);

                $totalPagos = 0;

                foreach ($paymentCols as $p) {
                    $abonoVal = (float)$sheet->getCell($p['amt'] . $row)->getCalculatedValue();
                    if ($abonoVal > 0) {
                        $pDateVal = $sheet->getCell($p['date'] . $row)->getCalculatedValue();
                        $pDate = $this->parseExcelDate($pDateVal);
                        
                        $pMethodVal = trim((string)$sheet->getCell($p['method'] . $row)->getCalculatedValue());
                        $methodMapped = 'otro';
                        $obs = null;
                        
                        if (stripos($pMethodVal, 'efectivo') !== false) {
                            $methodMapped = 'efectivo';
                        } elseif (stripos($pMethodVal, 'tran') !== false) {
                            $methodMapped = 'transferencia';
                        } elseif (stripos($pMethodVal, 'datafono') !== false || stripos($pMethodVal, 'datáfono') !== false) {
                            $methodMapped = 'datáfono';
                        } else {
                            $obs = 'Migrado de Excel. Método original: ' . ($pMethodVal ?: 'Ninguno');
                        }

                        Payment::create([
                            'invoice_id' => $invoice->id,
                            'amount' => $abonoVal,
                            'payment_date' => $pDate ?: ($invoiceDate ?: date('Y-m-d')),
                            'payment_method' => $methodMapped,
                            'observation' => $obs,
                        ]);

                        $totalPagos += $abonoVal;
                    }
                }

                // Validación de Cuadre
                $saldoCalculado = round($totalAmount - $discount - $totalPagos, 2);
                $saldoExcelRound = round($saldoExcel, 2);

                if ($saldoCalculado !== $saldoExcelRound) {
                    $this->error("\nDiscrepancia en factura {$invoiceNum}: Excel={$saldoExcelRound}, Calculado={$saldoCalculado}");
                    $validationErrors++;
                }

                $successCount++;
                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
            
            if ($validationErrors > 0) {
                DB::rollBack();
                $this->error("Se encontraron {$validationErrors} discrepancias. Se revirtió la base de datos a su estado original.");
            } else {
                DB::commit();
                $this->info("Importación finalizada exitosamente! Se procesaron {$successCount} facturas sin discrepancias matemáticas.");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error fatal durante la importación: " . $e->getMessage());
        }
    }

    private function parseExcelDate($value)
    {
        if (empty($value)) return null;
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
