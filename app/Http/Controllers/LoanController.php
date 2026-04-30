<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\Installment;
use App\Models\LoanProduct;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        // Eager load customer and specifically the first pending installment
        $query = Loan::with(['customer', 'installments' => function($q) {
            $q->where('status', 'pending')->orderBy('installment_number', 'asc')->limit(1);
        }]);
        
        if ($search = $request->query('search')) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($status = $request->query('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        return Inertia::render('Loans/Index', [
            'loans' => $query->orderBy('created_at', 'desc')->get()->map(function($loan) {
                $loanArray = $loan->toArray();
                $first = $loan->installments->first();
                if ($first) {
                    $loanArray['next_installment'] = [
                        'due_date' => Carbon::parse($first->due_date)->format('d/m/Y'),
                        'amount' => $first->amount,
                    ];
                } else {
                    $loanArray['next_installment'] = null;
                }
                unset($loanArray['installments']);
                return $loanArray;
            }),
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    public function exportPdf(Loan $loan)
    {
        $loan->load(['customer', 'installments', 'payments']);
        
        $pdf = Pdf::loadView('pdf.loan-details', compact('loan'));
        
        return $pdf->download("Prestamo_{$loan->id}_{$loan->customer->name}.pdf");
    }

    public function create()
    {
        return Inertia::render('Loans/Create', [
            'customers' => Customer::orderBy('name')->get(),
            'products' => LoanProduct::where('is_active', true)->orderBy('name')->get()
        ]);
    }

    public function show(Loan $loan)
    {
        return Inertia::render('Loans/Show', [
            'loan' => $loan->load(['customer', 'installments', 'payments'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'interest_rate' => 'required|numeric|min:0',
            'periodicity' => 'required|in:semanal,quincenal,mensual',
            'num_installments' => 'required|integer|min:1',
            'first_payment_date' => 'required|date',
            'promissory_note_folio' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            // Simple interest calculation (Interés Global)
            $interest = ($validated['amount'] * $validated['interest_rate']) / 100;
            $totalAmount = $validated['amount'] + $interest;
            $installmentAmount = round($totalAmount / $validated['num_installments'], 2);

            $loan = Loan::create([
                'customer_id' => $validated['customer_id'],
                'amount' => $validated['amount'],
                'interest_rate' => $validated['interest_rate'],
                'periodicity' => $validated['periodicity'],
                'num_installments' => $validated['num_installments'],
                'first_payment_date' => $validated['first_payment_date'],
                'promissory_note_folio' => $validated['promissory_note_folio'],
                'status' => 'active',
                'outstanding_balance' => $totalAmount,
            ]);

            $dueDate = Carbon::parse($validated['first_payment_date']);

            for ($i = 1; $i <= $validated['num_installments']; $i++) {
                Installment::create([
                    'loan_id' => $loan->id,
                    'installment_number' => $i,
                    'due_date' => $dueDate->toDateString(),
                    'amount' => $installmentAmount,
                    'status' => 'pending',
                ]);

                // Update date based on periodicity
                if ($validated['periodicity'] === 'semanal') {
                    $dueDate->addDays(7);
                } elseif ($validated['periodicity'] === 'quincenal') {
                    $dueDate->addDays(14);
                } elseif ($validated['periodicity'] === 'mensual') {
                    $dueDate->addMonth();
                }
            }

            return redirect()->route('loans.show', $loan->id)->with('success', 'Préstamo creado con éxito.');
        });
    }
}
