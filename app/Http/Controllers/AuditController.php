<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'No autorizado.');
        }

        $event = $request->query('event_type');
        $search = $request->query('search');

        $logs = AuditLog::with('user')
            ->when($event, fn($q) => $q->where('event_type', $event))
            ->when($search, function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($qu) => $qu->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%"));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(30)
            ->withQueryString();

        return view('audit.index', compact('logs', 'event', 'search'));
    }
}
