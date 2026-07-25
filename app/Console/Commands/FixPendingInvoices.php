<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;

class FixPendingInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:fix-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige el estado de las facturas/pólizas que están en Pendiente pero tienen saldo cero o negativo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando revisión de pólizas en estado Pendiente...');

        $invoices = Invoice::with(['payments', 'adjustments'])->where('status', 'pendiente')->get();
        
        $fixedCount = 0;

        foreach ($invoices as $invoice) {
            $totalPagado = $invoice->payments->sum('amount');
            $totalAjuste = $invoice->adjustments->sum('amount');
            
            // Calculamos el saldo tal como se hace en los controladores
            $saldo = $invoice->total_amount - $invoice->discount - $totalPagado - $totalAjuste;
            
            if ($saldo <= 0.01) {
                $invoice->update(['status' => 'pagada']);
                
                // Registramos en la auditoría para tener un historial del cambio
                \App\Helpers\AuditHelper::log(
                    'correccion_masiva_factura',
                    'Invoice',
                    $invoice->id,
                    "El sistema corrigió masivamente el estado a Pagada. Póliza #{$invoice->invoice_number} (Saldo real: $ {$saldo})"
                );

                $this->line("Corregida póliza #{$invoice->invoice_number} (ID: {$invoice->id})");
                $fixedCount++;
            }
        }

        $this->info("¡Proceso terminado! Se corrigieron un total de {$fixedCount} pólizas.");
    }
}
