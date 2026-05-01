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
        // Get the latest loan ID for each customer to work with active/recent cycles
        $latestLoanIds = Credito::select(DB::raw('MAX(id_credito) as id'))
            ->groupBy('id_cliente')
            ->pluck('id');

        $baseQuery = Credito::whereIn('id_credito', $latestLoanIds)
            ->with(['cliente', 'asesor'])
            ->withSum('pagos', 'monto');

        $query = clone $baseQuery;
        
        // Filtering
        if ($search = $request->query('search')) {
            $query->where(function($q) use ($search) {
                $q->where('id_credito', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function($c) use ($search) {
                      $c->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        if ($asesorId = $request->query('id_asesor')) {
            $query->where('id_asesor', $asesorId);
        }

        if ($status = $request->query('status')) {
            if ($status === 'pendiente') {
                $query->whereRaw('total > (SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE pagos.id_credito = creditos.id_credito)');
            } elseif ($status === 'liquidado') {
                $query->whereRaw('total <= (SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE pagos.id_credito = creditos.id_credito)');
            }
        }

        if ($fromDate = $request->query('from_date')) {
            $query->whereDate('fecha', '>=', $fromDate);
        }

        if ($toDate = $request->query('to_date')) {
            $query->whereDate('fecha', '<=', $toDate);
        }

        // Calculate statistics based on FILTERED results
        $filteredLoans = (clone $query)->get();
        $loanIds = $filteredLoans->pluck('id_credito');

        // Total payments made in the selected period for the filtered loans
        $paymentsInPeriod = Pago::whereIn('id_credito', $loanIds);
        if ($fromDate) $paymentsInPeriod->whereDate('fecha_pago', '>=', $fromDate);
        if ($toDate) $paymentsInPeriod->whereDate('fecha_pago', '<=', $toDate);
        $totalCobradoPeriodo = $paymentsInPeriod->sum('monto');

        // Calculate Collection Goal (Meta) based on payment days in the range
        $metaCobranza = 0;
        if ($fromDate && $toDate) {
            $start = Carbon::parse($fromDate);
            $end = Carbon::parse($toDate);
            $daysInRange = [];
            $current = $start->copy();
            while ($current <= $end) {
                $daysInRange[] = $current->dayOfWeek;
                $current->addDay();
                if (count($daysInRange) >= 7) break; // Optimization: all days included
            }

            $metaCobranza = $filteredLoans->filter(function($l) use ($daysInRange) {
                // Loan is active (not fully paid)
                $is_active = ($l->pagos_sum_monto ?? 0) < $l->total;
                if (!$is_active) return false;
                
                // Payment day (dayOfWeek of start date) is in the range
                $loanDay = Carbon::parse($l->fecha)->dayOfWeek;
                return in_array($loanDay, $daysInRange);
            })->sum('valor_ficha');
        } else {
            // Default: Total pending balance if no date filter
            $metaCobranza = $filteredLoans->sum(fn($l) => $l->total - ($l->pagos_sum_monto ?? 0));
        }

        $stats = [
            'total_cartera' => $filteredLoans->sum('total'),
            'total_cobrado' => $fromDate || $toDate ? $totalCobradoPeriodo : $filteredLoans->sum('pagos_sum_monto'),
            'total_pendiente' => $fromDate || $toDate ? max(0, $metaCobranza - $totalCobradoPeriodo) : $filteredLoans->sum(fn($l) => $l->total - ($l->pagos_sum_monto ?? 0)),
            'clientes_activos' => $filteredLoans->filter(fn($l) => ($l->pagos_sum_monto ?? 0) < $l->total)->count(),
            'meta_periodo' => $metaCobranza,
        ];

        return Inertia::render('Loans/Index', [
            'loans' => $query->orderBy('id_credito', 'desc')->get()->map(function($loan) {
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
            'stats' => $stats,
            'asesores' => Asesor::orderBy('nombre')->get(['id_asesor', 'nombre']),
            'filters' => $request->only(['search', 'id_asesor', 'status', 'from_date', 'to_date'])
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
