<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SettingController extends Controller
{
    /**
     * Actualiza la fecha de inicio y vencimiento de la membresía.
     * Solo accesible por administradores.
     */
    public function updateMembership(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'membership_starts_at' => 'required|date',
            'membership_expires_at' => 'required|date|after:membership_starts_at',
        ]);

        Setting::set('membership_starts_at', $validated['membership_starts_at']);
        Setting::set('membership_expires_at', $validated['membership_expires_at']);

        \App\Helpers\AuditHelper::log(
            'edicion_membresia',
            'Setting',
            1,
            "Actualizó la membresía: desde " . fecha_co($validated['membership_starts_at'])
                . " hasta " . fecha_co($validated['membership_expires_at'])
        );

        return back()->with('success', 'Membresía actualizada correctamente.');
    }
}
