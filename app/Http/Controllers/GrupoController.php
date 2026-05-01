<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Asesor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Grupos/Index', [
            'grupos' => Grupo::with(['asesor', 'clientes'])->get()->map(function($g) {
                return [
                    'id' => $g->id_grupo,
                    'nombre' => $g->nombre,
                    'asesor' => $g->asesor ? $g->asesor->nombre : 'Sin Asesor',
                    'clientes_count' => $g->clientes->count(),
                ];
            })
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Grupos/Create', [
            'asesores' => Asesor::all(['id_asesor as id', 'nombre'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'id_asesor' => 'required|exists:asesores,id_asesor',
        ]);

        Grupo::create($validated);

        return redirect()->route('grupos.index')->with('success', 'Grupo creado con éxito.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Grupo $grupo)
    {
        $grupo->load(['asesor', 'clientes.creditos']);
        
        // Get clients that don't belong to any group
        $availableClients = \App\Models\Cliente::whereNull('id_grupo')
            ->orderBy('nombre')
            ->get(['id_cliente as id', 'nombre']);

        return Inertia::render('Grupos/Show', [
            'grupo' => [
                'id' => $grupo->id_grupo,
                'nombre' => $grupo->nombre,
                'asesor' => $grupo->asesor->nombre,
                'clientes' => $grupo->clientes->map(function($c) {
                    return [
                        'id' => $c->id_cliente,
                        'nombre' => $c->nombre,
                        'creditos_count' => $c->creditos->count(),
                    ];
                }),
            ],
            'availableClients' => $availableClients
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Grupo $grupo)
    {
        return Inertia::render('Grupos/Edit', [
            'grupo' => $grupo,
            'asesores' => Asesor::all(['id_asesor as id', 'nombre'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'id_asesor' => 'required|exists:asesores,id_asesor',
        ]);

        $grupo->update($validated);

        return redirect()->route('grupos.index')->with('success', 'Grupo actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Grupo $grupo)
    {
        $grupo->delete();
        return redirect()->route('grupos.index')->with('success', 'Grupo eliminado.');
    }

    public function addClient(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'id_cliente' => 'required|exists:clientes,id_cliente',
        ]);

        $cliente = \App\Models\Cliente::find($validated['id_cliente']);
        $cliente->update(['id_grupo' => $grupo->id_grupo]);

        return back()->with('success', 'Cliente añadido al grupo.');
    }

    public function removeClient(Grupo $grupo, \App\Models\Cliente $cliente)
    {
        if ($cliente->id_grupo === $grupo->id_grupo) {
            $cliente->update(['id_grupo' => null]);
        }

        return back()->with('success', 'Cliente removido del grupo.');
    }
}
