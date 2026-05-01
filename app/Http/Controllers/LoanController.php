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
        $query = Credito::with(['cliente', 'asesor']);
        
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
                return [
                    'id' => $loan->id_credito,
                    'customer' => $loan->cliente,
                    'amount' => $loan->monto_otorgado,
                    'total' => $loan->total,
                    'fecha' => $loan->fecha ? Carbon::parse($loan->fecha)->format('d/m/Y') : 'N/A',
                    'ciclo' => $loan->ciclo,
                    'plazo' => $loan->plazo,
                    'asesor' => $loan->asesor ? $loan->asesor->nombre : 'Sin asesor',
                ];
            }),
            'filters' => $request->only(['search'])
        ]);
    }

    public function exportPdf(Credito $loan)
    {
        $loan->load(['cliente', 'pagos', 'asesor']);
        
        $pdf = Pdf::loadView('pdf.loan-details', compact('loan'));
        
        return $pdf->download("Prestamo_{$loan->id_credito}_{$loan->cliente->nombre}.pdf");
    }

    public function create()
    {
        return Inertia::render('Loans/Create', [
            'customers' => Cliente::orderBy('nombre')->get(),
            'asesores' => Asesor::orderBy('nombre')->get(),
        ]);
    }

    public function show(Credito $loan)
    {
        return Inertia::render('Loans/Show', [
            'loan' => $loan->load(['cliente', 'pagos', 'asesor'])
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
