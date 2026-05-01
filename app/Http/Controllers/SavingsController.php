<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\AhorroVoluntario;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SavingsController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->query('year', 2026);
        
        $asesores = Asesor::with(['ahorros' => function($q) use ($year) {
            $q->whereYear('fecha', $year);
        }])->orderBy('nombre')->get();

        $report = $asesores->map(function($asesor) {
            $months = array_fill(1, 12, 0);
            foreach ($asesor->ahorros as $ahorro) {
                $month = Carbon::parse($ahorro->fecha)->month;
                $months[$month] += (float)$ahorro->monto;
            }
            
            return [
                'id' => $asesor->id_asesor,
                'nombre' => $asesor->nombre,
                'months' => $months,
                'total' => array_sum($months)
            ];
        });

        return Inertia::render('Savings/Index', [
            'report' => $report,
            'year' => $year,
            'asesores' => Asesor::all(['id_asesor', 'nombre'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_asesor' => 'required|exists:asesores,id_asesor',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string'
        ]);

        AhorroVoluntario::create($validated);

        return redirect()->back()->with('success', 'Ahorro registrado correctamente');
    }

    public function exportPdf(Request $request)
    {
        $year = $request->query('year', 2026);
        $asesores = Asesor::with(['ahorros' => function($q) use ($year) {
            $q->whereYear('fecha', $year);
        }])->orderBy('nombre')->get();

        $report = $asesores->map(function($asesor) {
            $months = array_fill(1, 12, 0);
            foreach ($asesor->ahorros as $ahorro) {
                $month = Carbon::parse($ahorro->fecha)->month;
                $months[$month] += (float)$ahorro->monto;
            }
            return [
                'id' => $asesor->id_asesor,
                'nombre' => $asesor->nombre,
                'months' => $months,
                'total' => array_sum($months)
            ];
        });

        $pdf = Pdf::loadView('pdf.savings-report', compact('report', 'year'))->setPaper('a4', 'landscape');
        return $pdf->download("Resumen_Ahorro_{$year}.pdf");
    }
}
