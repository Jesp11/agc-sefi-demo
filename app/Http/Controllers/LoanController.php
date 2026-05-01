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
        $fromDate = $request->query('from_date', Carbon::today()->format('Y-m-d'));
        $toDate = $request->query('to_date', Carbon::today()->format('Y-m-d'));

        // Get the latest loan ID for each customer to work with active/recent cycles
        $latestLoanIds = Credito::select(DB::raw('MAX(id_credito) as id'))
            ->groupBy('id_cliente')
            ->pluck('id');

        $baseQuery = Credito::whereIn('id_credito', $latestLoanIds)
            ->with(['cliente.grupo', 'asesor'])
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

        $allLoans = (clone $query)->get();

        // Calculate days in range to filter loans
        $start = Carbon::parse($fromDate);
        $end = Carbon::parse($toDate);
        $daysInRange = [];
        $current = $start->copy();
        while ($current <= $end) {
            $daysInRange[] = $current->dayOfWeek;
            $current->addDay();
            if (count($daysInRange) >= 7) break; // Optimization: all days included
        }

        // Filter loans that have a payment due in the date range
        $filteredLoans = $allLoans->filter(function($l) use ($daysInRange) {
            $loanDay = Carbon::parse($l->fecha)->dayOfWeek;
            return in_array($loanDay, $daysInRange);
        });

        $loanIds = $filteredLoans->pluck('id_credito');

        // Fetch all payments for these loans to process in memory
        $allPaymentsForLoans = Pago::whereIn('id_credito', $loanIds)->get();
        
        // Payments specifically made within the selected range (for stats)
        $paymentsInPeriod = $allPaymentsForLoans->filter(function($p) use ($fromDate, $toDate) {
            $pDate = Carbon::parse($p->fecha_pago)->format('Y-m-d');
            return $pDate >= $fromDate && $pDate <= $toDate;
        });

        $totalCobradoPeriodo = $paymentsInPeriod->sum('monto');
        
        // Calculate meta and mapped loans
        $mappedLoans = $filteredLoans->map(function($loan) use ($toDate) {
            $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $dia_pago = $loan->fecha ? $days[Carbon::parse($loan->fecha)->dayOfWeek] : 'N/A';
            
            $pagado = $loan->pagos_sum_monto ?? 0;
            $completado = $pagado >= $loan->total;

            // Logical check: Is the installment for this period already covered?
            // We calculate how many weeks have passed since the start until the filtered 'toDate'
            $startDate = Carbon::parse($loan->fecha);
            $targetDate = Carbon::parse($toDate);
            
            // If the target date is before the start date, it's not due yet
            if ($targetDate->lt($startDate)) {
                $cobradoEnPeriodo = true; // Or perhaps hide it, but for now we mark as "handled"
            } else {
                $weeksPassed = floor($startDate->diffInDays($targetDate) / 7);
                // The installment for 'today' (targetDate) is covered if total paid is at least $weeksPassed * valor_ficha
                $expectedAmountByNow = $weeksPassed * $loan->valor_ficha;
                
                $cobradoEnPeriodo = $pagado >= $expectedAmountByNow;
            }

            return [
                'id' => $loan->id_credito,
                'customer' => $loan->cliente,
                'grupo' => $loan->cliente && $loan->cliente->grupo ? $loan->cliente->grupo->nombre : null,
                'amount' => $loan->monto_otorgado,
                'interes' => $loan->interes,
                'valor_ficha' => $loan->valor_ficha,
                'total' => $loan->total,
                'pagado' => $pagado,
                'pendiente' => max(0, $loan->total - $pagado),
                'completado' => $completado,
                'cobrado_en_periodo' => $cobradoEnPeriodo || $completado,
                'fecha' => $loan->fecha ? Carbon::parse($loan->fecha)->format('d/m/Y') : 'N/A',
                'dia_pago' => $dia_pago,
                'ciclo' => $loan->ciclo,
                'plazo' => $loan->plazo,
                'asesor' => $loan->asesor ? $loan->asesor->nombre : 'Sin asesor',
            ];
        });

        // Re-calculate stats based on mapped loans
        $metaCobranza = $mappedLoans->filter(fn($l) => !$l['cobrado_en_periodo'])->sum('valor_ficha');

        $stats = [
            'total_cartera' => $filteredLoans->sum('total'),
            'total_cobrado' => $totalCobradoPeriodo,
            'total_pendiente' => $metaCobranza,
            'clientes_activos' => $mappedLoans->filter(fn($l) => !$l['completado'])->count(),
            'meta_periodo' => $metaCobranza + $totalCobradoPeriodo,
        ];

        // Sort loans so that those with groups appear grouped together and alphabetically by group name
        $sortedLoans = $mappedLoans->sortBy(function($loan) {
            return $loan['grupo'] ? $loan['grupo'] : 'ZZZZZZZZ';
        })->values();

        return Inertia::render('Loans/Index', [
            'loans' => $sortedLoans,
            'stats' => $stats,
            'asesores' => Asesor::orderBy('nombre')->get(['id_asesor', 'nombre']),
            'filters' => array_merge($request->only(['search', 'id_asesor', 'status']), [
                'from_date' => $fromDate,
                'to_date' => $toDate
            ])
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
