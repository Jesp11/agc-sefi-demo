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
        $grupo->load(['asesor', 'clientes.creditos.pagos', 'clientes.creditos.asesor']);
        
        // Get clients that don't belong to any group
        $availableClients = \App\Models\Cliente::whereNull('id_grupo')
            ->orderBy('nombre')
            ->get(['id_cliente as id', 'nombre']);

        return Inertia::render('Grupos/Show', [
            'grupo' => [
                'id' => $grupo->id_grupo,
                'nombre' => $grupo->nombre,
                'asesor' => $grupo->asesor->nombre,
                'id_responsable' => $grupo->id_responsable,
                'clientes' => $grupo->clientes->map(function($c) {
                    $latestLoan = $c->creditos()->orderBy('id_credito', 'desc')->first();
                    
                    if ($latestLoan) {
                        $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                        $dia_pago = $latestLoan->fecha ? $days[\Carbon\Carbon::parse($latestLoan->fecha)->dayOfWeek] : 'N/A';
                    }

                    return [
                        'id_cliente' => $c->id_cliente,
                        'nombre' => $c->nombre,
                        'telefono' => $c->telefono,
                        'latest_loan' => $latestLoan ? [
                            'id' => $latestLoan->id_credito,
                            'amount' => $latestLoan->monto_otorgado,
                            'interes' => $latestLoan->interes,
                            'valor_ficha' => $latestLoan->valor_ficha,
                            'total' => $latestLoan->total,
                            'pagado' => $latestLoan->pagos->sum('monto'),
                            'completado' => $latestLoan->pagos->sum('monto') >= $latestLoan->total,
                            'fecha' => $latestLoan->fecha,
                            'dia_pago' => $dia_pago,
                            'plazo' => $latestLoan->plazo,
                            'asesor' => $latestLoan->asesor ? $latestLoan->asesor->nombre : 'Sin asesor',
                        ] : null,
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

    public function createLoans(Grupo $grupo)
    {
        $grupo->load(['clientes']);
        
        return Inertia::render('Loans/GroupCreate', [
            'grupo' => [
                'id' => $grupo->id_grupo,
                'nombre' => $grupo->nombre,
                'id_asesor' => $grupo->id_asesor,
                'id_responsable' => $grupo->id_responsable,
                'clientes' => $grupo->clientes->map(fn($c) => [
                    'id' => $c->id_cliente,
                    'nombre' => $c->nombre,
                ]),
            ],
            'asesores' => Asesor::orderBy('nombre')->get(['id_asesor as id', 'nombre']),
        ]);
    }

    public function storeLoans(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'id_asesor' => 'required|exists:asesores,id_asesor',
            'id_responsable' => 'nullable|exists:clientes,id_cliente',
            'fecha' => 'required|date',
            'plazo' => 'required|integer|min:1',
            'prestamos' => 'required|array',
            'prestamos.*.id_cliente' => 'required|exists:clientes,id_cliente',
            'prestamos.*.monto_otorgado' => 'required|numeric|min:0',
            'prestamos.*.valor_ficha' => 'required|numeric|min:0',
        ]);

        // Update group responsible if provided
        if (isset($validated['id_responsable'])) {
            $grupo->update(['id_responsable' => $validated['id_responsable']]);
        }

        foreach ($validated['prestamos'] as $prestamo) {
            $monto = $prestamo['monto_otorgado'];
            $ficha = $prestamo['valor_ficha'];
            $plazo = $validated['plazo'];
            
            $total = $ficha * $plazo;
            $interes = $total - $monto;

            // Calculate auto cycle based on max previous cycle for this client
            $last_cycle = \App\Models\Credito::where('id_cliente', $prestamo['id_cliente'])->max('ciclo') ?? 0;

            \App\Models\Credito::create([
                'id_cliente' => $prestamo['id_cliente'],
                'monto_otorgado' => $monto,
                'interes' => $interes,
                'total' => $total,
                'plazo' => $plazo,
                'fecha' => $validated['fecha'],
                'id_asesor' => $validated['id_asesor'],
                'ciclo' => $last_cycle + 1,
                'valor_ficha' => $ficha,
            ]);
        }

        return redirect()->route('grupos.show', $grupo->id_grupo)->with('success', 'Préstamos grupales creados con éxito.');
    }
}
