<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $clients = Client::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->withCount('invoices')
            ->withSum('invoices', 'total_amount')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('clients.index', compact('clients', 'search'));
    }
    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:clients,name',
            'phone'       => 'nullable|string|max:30',
            'document_id' => 'nullable|string|max:30',
        ]);

        $client = Client::create($validated);

        \App\Helpers\AuditHelper::log(
            'creacion_cliente',
            'Client',
            $client->id,
            "Registró al cliente \"{$client->name}\" (" . ($client->document_id ?: 'Sin documento') . ")"
        );

        return redirect()->route('clients.index')
            ->with('success', "Cliente registrado exitosamente.");
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:clients,name,' . $client->id,
            'phone'       => 'nullable|string|max:30',
            'document_id' => 'nullable|string|max:30',
        ]);

        $oldName = $client->name;
        $client->update($validated);

        \App\Helpers\AuditHelper::log(
            'edicion_cliente',
            'Client',
            $client->id,
            "Actualizó datos del cliente \"{$oldName}\"" . ($oldName !== $client->name ? " a \"{$client->name}\"" : "")
        );

        return redirect()->route('clients.index')
            ->with('success', "Cliente \"{$client->name}\" actualizado correctamente.");
    }
}
