<?php

if (!function_exists('fecha_co')) {
    /**
     * Formatea una fecha al formato colombiano dd/mm/aaaa.
     * Acepta strings tipo '2024-01-15', objetos Carbon, o timestamps.
     *
     * @param  mixed  $fecha
     * @param  bool   $conHora  Si true, incluye la hora en formato HH:mm
     * @return string
     */
    function fecha_co($fecha, bool $conHora = false): string
    {
        if (empty($fecha)) {
            return '—';
        }

        try {
            $carbon = \Carbon\Carbon::parse($fecha)->setTimezone('America/Bogota');
            return $conHora
                ? $carbon->format('d/m/Y H:i')
                : $carbon->format('d/m/Y');
        } catch (\Throwable $e) {
            return (string) $fecha;
        }
    }
}

if (!function_exists('fecha_co_input')) {
    /**
     * Convierte una fecha al formato Y-m-d para usar en inputs type="date".
     *
     * @param  mixed  $fecha
     * @return string
     */
    function fecha_co_input($fecha): string
    {
        if (empty($fecha)) {
            return date('Y-m-d');
        }
        try {
            return \Carbon\Carbon::parse($fecha)->format('Y-m-d');
        } catch (\Throwable $e) {
            return date('Y-m-d');
        }
    }
}

if (!function_exists('dinero')) {
    /**
     * Formatea un valor numérico a moneda (pesos colombianos).
     *
     * @param  mixed  $valor
     * @return string
     */
    function dinero($valor): string
    {
        return '$ ' . number_format($valor ?? 0, 0, ',', '.');
    }
}

