<?php
/*
 * File: app/Http/Controllers/AsesorController.php
 */

namespace App\Http\Controllers;

use App\Models\Asesor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AsesorController extends Controller
{
    public function index()
    {
        return Inertia::render('Asesores/Index', [
            'asesores' => Asesor::orderBy('nombre')->get()->map(function($a) {
                return [
                    'id' => $a->id_asesor,
                    'nombre' => $a->nombre,
                    'creditos_count' => $a->creditos()->count(),
                ];
            })
        ]);
    }

    public function create()
    {
        return Inertia::render('Asesores/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        Asesor::create($validated);

        return redirect()->route('asesores.index')->with('success', 'Asesor registrado con éxito.');
    }

    public function show(Asesor $asesor)
    {
        $asesor->load([
            'ahorros' => function($q) {
                $q->orderBy('fecha', 'desc');
            }, 
            'creditos.cliente',
            'creditosGrupales.cliente',
            'creditosGrupales.grupo'
        ]);

        return Inertia::render('Asesores/Show', [
            'asesor' => [
                'id' => $asesor->id_asesor,
                'nombre' => $asesor->nombre,
                'ahorros' => $asesor->ahorros,
                'creditos' => $asesor->creditos,
                'creditos_grupales' => $asesor->creditosGrupales,
                'total_ahorro' => $asesor->ahorros->sum('monto')
            ]
        ]);
    }

    public function edit(Asesor $asesor)
    {
        return Inertia::render('Asesores/Edit', [
            'asesor' => [
                'id' => $asesor->id_asesor,
                'nombre' => $asesor->nombre,
            ]
        ]);
    }

    public function update(Request $request, Asesor $asesor)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $asesor->update($validated);

        return redirect()->route('asesores.index')->with('success', 'Información del asesor actualizada.');
    }

    public function destroy(Asesor $asesor)
    {
        if ($asesor->creditos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un asesor con créditos asociados.');
        }

        $asesor->delete();

        return redirect()->route('asesores.index')->with('success', 'Asesor eliminado correctamente.');
    }
}
