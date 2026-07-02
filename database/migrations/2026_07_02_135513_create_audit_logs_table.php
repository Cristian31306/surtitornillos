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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('event_type'); // 'creacion_cliente', 'creacion_factura', 'edicion_factura', 'abono_registrado', 'abono_eliminado', 'ajuste_registrado', 'ajuste_eliminado', 'anulacion_factura'
            $table->string('target_type'); // 'Client', 'Invoice', 'Payment', 'Adjustment'
            $table->unsignedBigInteger('target_id');
            $table->text('description'); // ej: "Usuario admin registró abono de $50.000 a la factura #F-102"
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
