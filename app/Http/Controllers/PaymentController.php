<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Payment;
use App\Models\Installment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        return DB::transaction(function () use ($validated) {
            $loan = Loan::with('installments')->findOrFail($validated['loan_id']);
            
            // Create payment record
            Payment::create($validated);

            // Update loan balance
            $loan->outstanding_balance -= $validated['amount'];
            if ($loan->outstanding_balance <= 0) {
                $loan->outstanding_balance = 0;
                $loan->status = 'liquidated';
            }
            $loan->save();

            // Apply payment to installments (FIFO)
            $remainingPayment = $validated['amount'];
            $pendingInstallments = $loan->installments()
                ->where('status', 'pending')
                ->orderBy('installment_number')
                ->get();

            foreach ($pendingInstallments as $installment) {
                if ($remainingPayment <= 0) break;

                // For this MVP, we consider an installment "paid" if the payment covers it.
                // In a more complex system, we'd handle partial payments per installment.
                // Here, we just mark as paid if we have enough from this or previous payments.
                if ($remainingPayment >= $installment->amount) {
                    $installment->status = 'paid';
                    $installment->paid_at = $validated['payment_date'];
                    $installment->save();
                    $remainingPayment -= $installment->amount;
                } else {
                    // Partial payment for the last installment reached
                    // Note: Simplified logic for MVP - we only mark as paid if fully covered.
                    // But we still subtract from balance.
                    break;
                }
            }

            return redirect()->back()->with('success', 'Pago registrado correctamente.');
        });
    }
}
