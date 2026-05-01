<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Asesor;
use App\Models\Pago;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $query = Credito::with(['cliente', 'asesor'])->withSum('pagos', 'monto');
        
        if ($search = $request->query('search')) {
            $query->where(function($q) use ($search) {
                $q->where('id_credito', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function($c) use ($search) {
                      $c->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        return Inertia::render('Loans/Index', [
            'loans' => $query->orderBy('fecha', 'desc')->get()->map(function($loan) {
                $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                $dia_pago = $loan->fecha ? $days[Carbon::parse($loan->fecha)->dayOfWeek] : 'N/A';
                
                $pagado = $loan->pagos_sum_monto ?? 0;
                $completado = $pagado >= $loan->total;

                return [
                    'id' => $loan->id_credito,
                    'customer' => $loan->cliente,
                    'amount' => $loan->monto_otorgado,
                    'interes' => $loan->interes,
                    'valor_ficha' => $loan->valor_ficha,
                    'total' => $loan->total,
                    'pagado' => $pagado,
                    'completado' => $completado,
                    'fecha' => $loan->fecha ? Carbon::parse($loan->fecha)->format('d/m/Y') : 'N/A',
                    'dia_pago' => $dia_pago,
                    'ciclo' => $loan->ciclo,
                    'plazo' => $loan->plazo,
                    'asesor' => $loan->asesor ? $loan->asesor->nombre : 'Sin asesor',
                ];
            }),
            'filters' => $request->only(['search'])
        ]);
    }

    public function exportPdf(Request $request, Credito $loan)
    {
        $loan->load(['cliente', 'pagos', 'asesor']);
        $type = $request->query('type', 'admin');
        
        $view = $type === 'client' ? 'pdf.loan-statement' : 'pdf.loan-details';
        $filename = $type === 'client' ? "Estado_Cuenta_" : "Expediente_";
        
        $pdf = Pdf::loadView($view, compact('loan'));
        
        return $pdf->download($filename . "{$loan->id_credito}_{$loan->cliente->nombre}.pdf");
    }

    public function create()
    {
        return Inertia::render('Loans/Create', [
            'customers' => Cliente::withMax('creditos', 'ciclo')->orderBy('nombre')->get()->map(fn($c) => [
                'id' => $c->id_cliente,
                'name' => $c->nombre,
                'last_cycle' => $c->creditos_max_ciclo ?? 0,
                'id_asesor' => $c->id_asesor,
            ]),
            'asesores' => Asesor::orderBy('nombre')->get()->map(fn($a) => [
                'id' => $a->id_asesor,
                'name' => $a->nombre,
            ]),
            'products' => [], // Add empty products if needed to avoid undefined props
        ]);
    }

    public function show(Credito $loan)
    {
        $loan->load(['cliente', 'pagos', 'asesor']);
        
        $total_pagado = $loan->pagos->sum('monto');
        $ganancia = max(0, $total_pagado - $loan->monto_otorgado);

        return Inertia::render('Loans/Show', [
            'loan' => $loan,
            'total_pagado' => $total_pagado,
            'ganancia' => $ganancia,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'monto_otorgado' => 'required|numeric|min:0',
            'interes' => 'required|numeric|min:0',
            'plazo' => 'required|integer|min:1',
            'fecha' => 'required|date',
            'id_asesor' => 'nullable|exists:asesores,id_asesor',
            'ciclo' => 'nullable|integer',
            'dias_pago' => 'nullable|integer',
            'num_prog' => 'nullable|integer',
            'valor_ficha' => 'nullable|numeric',
        ]);

        $validated['total'] = $validated['monto_otorgado'] + $validated['interes'];

        $loan = Credito::create($validated);

        return redirect()->route('loans.index')->with('success', 'Crédito creado con éxito.');
    }
}
