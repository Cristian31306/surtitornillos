<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_invoices_summary');

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->nullOnDelete();
        });

        $sql = "CREATE VIEW v_invoices_summary AS
 SELECT 
 i.id AS invoice_id,
 i.invoice_number,
 i.issue_date,
 c.id AS client_id,
 c.name AS client_name,
 i.total_amount,
 i.discount,
 (i.total_amount - i.discount) AS net_amount,
 COALESCE(p.total_payments, 0) AS total_payments,
 COALESCE(a.total_adjustments, 0) AS total_adjustments,
 (i.total_amount - i.discount - COALESCE(p.total_payments, 0) - COALESCE(a.total_adjustments, 0)) AS current_balance,
 i.status AS configured_status
 FROM invoices i
 JOIN clients c ON i.client_id = c.id
 LEFT JOIN (
 SELECT invoice_id, SUM(amount) AS total_payments 
 FROM payments 
 GROUP BY invoice_id
 ) p ON i.id = p.invoice_id
 LEFT JOIN (
 SELECT invoice_id, SUM(amount) AS total_adjustments 
 FROM adjustments 
 GROUP BY invoice_id
 ) a ON i.id = a.invoice_id";
        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_invoices_summary');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropColumn('seller_id');
        });

        $sql = "CREATE VIEW v_invoices_summary AS
 SELECT 
 i.id AS invoice_id,
 i.invoice_number,
 i.issue_date,
 c.id AS client_id,
 c.name AS client_name,
 i.total_amount,
 i.discount,
 (i.total_amount - i.discount) AS net_amount,
 COALESCE(p.total_payments, 0) AS total_payments,
 COALESCE(a.total_adjustments, 0) AS total_adjustments,
 (i.total_amount - i.discount - COALESCE(p.total_payments, 0) - COALESCE(a.total_adjustments, 0)) AS current_balance,
 i.status AS configured_status
 FROM invoices i
 JOIN clients c ON i.client_id = c.id
 LEFT JOIN (
 SELECT invoice_id, SUM(amount) AS total_payments 
 FROM payments 
 GROUP BY invoice_id
 ) p ON i.id = p.invoice_id
 LEFT JOIN (
 SELECT invoice_id, SUM(amount) AS total_adjustments 
 FROM adjustments 
 GROUP BY invoice_id
 ) a ON i.id = a.invoice_id";
        DB::statement($sql);
    }
};
