<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('restrict');
            $table->string('invoice_number')->unique();
            $table->date('issue_date')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->enum('status', ['pendiente', 'pagada', 'anulada'])->default('pendiente');
            $table->text('observation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
