<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        // Solo administradores pueden ver y gestionar usuarios
        if (!Auth::user()->isAdmin()) {
            abort(403, 'No autorizado.');
        }

        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'name'     => 'required|string|max:255',
            'password' => 'required|string|min:4',
            'role'     => 'required|in:admin,colaborador',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        \App\Helpers\AuditHelper::log(
            'creacion_usuario',
            'User',
            $user->id,
            "Creó el usuario \"{$user->username}\" con rol \"{$user->role}\""
        );

        return back()->with('success', "Usuario \"{$user->username}\" creado con éxito.");
    }

    public function update(Request $request, User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'No autorizado.');
        }

        // Evitar que el admin se quite el rol admin o se auto-desactive
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'nullable|string|min:4',
            'role'     => 'required|in:admin,colaborador',
        ]);

        if ($user->id === Auth::id() && $validated['role'] !== 'admin') {
            return back()->with('error', 'No puedes degradar tu propio usuario administrador.');
        }

        $user->name = $validated['name'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        \App\Helpers\AuditHelper::log(
            'edicion_usuario',
            'User',
            $user->id,
            "Actualizó los datos del usuario \"{$user->username}\""
        );

        return back()->with('success', "Usuario \"{$user->username}\" actualizado.");
    }

    public function destroy(User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'No autorizado.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario en sesión.');
        }

        $username = $user->username;
        $user->delete();

        \App\Helpers\AuditHelper::log(
            'eliminacion_usuario',
            'User',
            $user->id,
            "Eliminó al usuario \"{$username}\""
        );

        return back()->with('success', "Usuario \"{$username}\" eliminado correctamente.");
    }
}
