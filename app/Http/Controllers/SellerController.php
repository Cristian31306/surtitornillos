<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SellerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sellers = Seller::withCount('invoices')->orderBy('name')->get();

        // Calcular métricas del mes actual para los vendedores
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Total facturado este mes
        $invoicedThisMonth = DB::table('invoices')
            ->select('seller_id', DB::raw('SUM(total_amount - discount) as total_invoiced'))
            ->whereBetween('issue_date', [$startOfMonth, $endOfMonth])
            ->whereNotNull('seller_id')
            ->groupBy('seller_id')
            ->pluck('total_invoiced', 'seller_id');

        // Total recaudado este mes (abonos de facturas de ese vendedor)
        $collectedThisMonth = DB::table('payments')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->select('invoices.seller_id', DB::raw('SUM(payments.amount) as total_collected'))
            ->whereBetween('payments.payment_date', [$startOfMonth, $endOfMonth])
            ->whereNotNull('invoices.seller_id')
            ->groupBy('invoices.seller_id')
            ->pluck('total_collected', 'seller_id');

        foreach ($sellers as $seller) {
            $seller->invoiced_this_month = $invoicedThisMonth[$seller->id] ?? 0;
            $seller->collected_this_month = $collectedThisMonth[$seller->id] ?? 0;
        }

        return view('sellers.index', compact('sellers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_id' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:activo,inactivo'
        ]);

        $seller = Seller::create($validated);
        \App\Helpers\AuditHelper::log('creacion_vendedor', 'Seller', $seller->id, "Vendedor creado: {$seller->name}");

        return redirect()->route('sellers.index')->with('success', 'Vendedor creado correctamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_id' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:activo,inactivo'
        ]);

        $seller->update($validated);
        \App\Helpers\AuditHelper::log('edicion_vendedor', 'Seller', $seller->id, "Vendedor actualizado: {$seller->name}");

        return redirect()->route('sellers.index')->with('success', 'Vendedor actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Seller $seller)
    {
        if ($seller->invoices()->exists()) {
            return redirect()->route('sellers.index')->with('error', 'No se puede eliminar el vendedor porque tiene facturas asociadas.');
        }

        $sellerName = $seller->name;
        $sellerId = $seller->id;
        $seller->delete();

        \App\Helpers\AuditHelper::log('eliminacion_vendedor', 'Seller', $sellerId, "Vendedor eliminado: {$sellerName}");

        return redirect()->route('sellers.index')->with('success', 'Vendedor eliminado correctamente.');
    }
}
