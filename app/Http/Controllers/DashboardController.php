<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\Loan;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();

        return Inertia::render('Dashboard', [
            'metrics' => [
                'total_customers' => Customer::count(),
                'active_loans' => Loan::where('status', 'active')->count(),
                'total_portfolio' => Loan::where('status', 'active')->sum('outstanding_balance'),
                'monthly_collection' => \App\Models\Payment::where('payment_date', '>=', $startOfMonth)->sum('amount'),
                'overdue_amount' => \App\Models\Installment::where('status', 'pending')
                    ->where('due_date', '<', $now->toDateString())
                    ->sum('amount'),
                'upcoming_amount' => \App\Models\Installment::where('status', 'pending')
                    ->where('due_date', '>=', $now->toDateString())
                    ->where('due_date', '<=', $now->copy()->addDays(7)->toDateString())
                    ->sum('amount'),
            ]
        ]);
    }
}
