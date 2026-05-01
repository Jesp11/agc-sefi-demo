<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Pago;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();

        return Inertia::render('Dashboard', [
            'metrics' => [
                'total_customers' => Cliente::count(),
                'active_loans' => Credito::count(),
                'total_portfolio' => Credito::sum('total'),
                'monthly_collection' => Pago::where('fecha_pago', '>=', $startOfMonth)->sum('monto'),
                'overdue_amount' => 0, // Simplified for now
                'upcoming_amount' => 0, // Simplified for now
            ]
        ]);
    }
}
