<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // KPIs principales
        $kpis = DB::selectOne("
            SELECT
                SUM(total_amount)                                                      AS total_facturado,
                SUM(net_amount)                                                        AS total_neto,
                SUM(total_payments)                                                    AS total_cobrado,
                SUM(current_balance)                                                   AS total_pendiente,
                COUNT(*)                                                               AS total_facturas,
                SUM(CASE WHEN configured_status = 'pendiente' THEN 1 ELSE 0 END)      AS facturas_pendientes,
                SUM(CASE WHEN configured_status = 'pagada'    THEN 1 ELSE 0 END)      AS facturas_pagadas,
                SUM(CASE WHEN current_balance  > 0            THEN 1 ELSE 0 END)      AS con_saldo,
                COUNT(DISTINCT client_id)                                              AS total_clientes
            FROM v_invoices_summary
        ");

        // Porcentaje de recaudo
        $kpis->porcentaje_recaudo = $kpis->total_neto > 0
            ? round(($kpis->total_cobrado / $kpis->total_neto) * 100, 1)
            : 0;

        // Top 5 clientes con mayor cartera pendiente
        $topDeudores = DB::select("
            SELECT client_name, SUM(current_balance) AS saldo_total, COUNT(*) AS num_facturas
            FROM v_invoices_summary
            WHERE configured_status = 'pendiente' AND current_balance > 0
            GROUP BY client_name
            ORDER BY saldo_total DESC
            LIMIT 5
        ");

        // Facturas más antiguas pendientes (las que llevan más tiempo sin pagar)
        $facturasAntiguas = DB::table('v_invoices_summary')
            ->where('configured_status', 'pendiente')
            ->where('current_balance', '>', 0)
            ->whereNotNull('issue_date')
            ->orderBy('issue_date', 'asc')
            ->limit(5)
            ->get();

        // Últimas facturas registradas (actividad reciente)
        $recentInvoices = DB::table('v_invoices_summary')
            ->orderByDesc('issue_date')
            ->limit(8)
            ->get();

        return view('dashboard', compact('kpis', 'recentInvoices', 'topDeudores', 'facturasAntiguas'));
    }
}
