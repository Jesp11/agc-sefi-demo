<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Installment;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'day');
        $startDateParam = $request->query('start_date');
        $endDateParam = $request->query('end_date');
        
        $data = $this->getReportData($period, $startDateParam, $endDateParam);
        $data['current_period'] = $period;
        $data['custom_start_date'] = $startDateParam;
        $data['custom_end_date'] = $endDateParam;
        
        return Inertia::render('Reports/Index', $data);
    }

    public function center()
    {
        return Inertia::render('Reports/Center');
    }

    public function exportPdf(Request $request)
    {
        $period = $request->query('period', 'day');
        $startDateParam = $request->query('start_date');
        $endDateParam = $request->query('end_date');
        
        $data = $this->getReportData($period, $startDateParam, $endDateParam);
        $data['current_period'] = $period;
        $data['custom_start_date'] = $startDateParam;
        $data['custom_end_date'] = $endDateParam;
        
        $pdf = Pdf::loadView('pdf.operation-report', $data);
        return $pdf->download('Estado_de_Operacion.pdf');
    }

    public function upcoming()
    {
        $today = Carbon::today();
        $targetDate = $today->copy()->addDay();
        
        $upcomingInstallments = Installment::with(['loan.customer'])
            ->where('due_date', $targetDate->toDateString())
            ->where('status', 'pending')
            ->get()->map(fn($i) => [
                'id' => $i->id,
                'customer' => $i->loan->customer->name,
                'loan_id' => $i->loan->id,
                'amount' => $i->amount,
                'installment_number' => $i->installment_number,
                'due_date' => Carbon::parse($i->due_date)->format('d/m/Y')
            ]);
            
        return Inertia::render('Reports/Upcoming', [
            'upcoming_installments' => $upcomingInstallments,
            'target_date' => $targetDate->format('d/m/Y')
        ]);
    }

    public function overdue()
    {
        $today = Carbon::today()->toDateString();
        
        $overdueInstallments = Installment::with(['loan.customer'])
            ->where('due_date', '<', $today)
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->get();
            
        return Inertia::render('Reports/Overdue', [
            'installments' => $overdueInstallments,
            'total_amount' => $overdueInstallments->sum('amount')
        ]);
    }

    public function exportUpcomingPdf(Request $request)
    {
        $period = $request->query('period', 'tomorrow');
        $customStartDate = $request->query('start_date');
        $customEndDate = $request->query('end_date');
        $today = Carbon::today();

        $dateText = '';
        switch ($period) {
            case 'custom':
                $startDate = $customStartDate ? Carbon::parse($customStartDate)->startOfDay() : $today->copy()->startOfDay();
                $endDate = $customEndDate ? Carbon::parse($customEndDate)->endOfDay() : $today->copy()->addDay()->endOfDay();
                $dateText = $startDate->format('d/m/Y') . ' al ' . $endDate->format('d/m/Y');
                break;
            case 'week':
                $startDate = $today->copy()->startOfWeek();
                $endDate = $today->copy()->endOfWeek();
                $dateText = 'Semana (' . $startDate->format('d/m/Y') . ' al ' . $endDate->format('d/m/Y') . ')';
                break;
            case 'today':
                $startDate = $today->copy()->startOfDay();
                $endDate = $today->copy()->endOfDay();
                $dateText = $startDate->format('d/m/Y') . ' (Hoy)';
                break;
            case 'tomorrow':
            default:
                $startDate = $today->copy()->addDay()->startOfDay();
                $endDate = $today->copy()->addDay()->endOfDay();
                $dateText = $startDate->format('d/m/Y') . ' (Mañana)';
                break;
        }
        
        $upcomingInstallments = Installment::with(['loan.customer'])
            ->whereBetween('due_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->get();
            
        $data = [
            'date' => $dateText,
            'installments' => $upcomingInstallments,
            'total' => $upcomingInstallments->sum('amount')
        ];
        
        $pdf = Pdf::loadView('pdf.upcoming-report', $data);
        return $pdf->download('Pagos_Proyectados.pdf');
    }

    private function getReportData($period, $customStartDate = null, $customEndDate = null)
    {
        $today = Carbon::today();
        
        // Define Period Constraints
        switch ($period) {
            case 'custom':
                $startDate = $customStartDate ? Carbon::parse($customStartDate)->startOfDay() : $today->copy()->startOfDay();
                $endDate = $customEndDate ? Carbon::parse($customEndDate)->endOfDay() : $today->copy()->endOfDay();
                break;
            case 'week':
                $startDate = $today->copy()->startOfWeek();
                $endDate = $today->copy()->endOfWeek();
                break;
            case 'fortnight':
                if ($today->day <= 15) {
                    $startDate = $today->copy()->startOfMonth();
                    $endDate = $today->copy()->setDay(15)->endOfDay();
                } else {
                    $startDate = $today->copy()->setDay(16)->startOfDay();
                    $endDate = $today->copy()->endOfMonth();
                }
                break;
            case 'month':
                $startDate = $today->copy()->startOfMonth();
                $endDate = $today->copy()->endOfMonth();
                break;
            case 'quarter':
                $startDate = $today->copy()->firstOfQuarter();
                $endDate = $today->copy()->lastOfQuarter()->endOfDay();
                break;
            case 'semester':
                if ($today->month <= 6) {
                    $startDate = $today->copy()->startOfYear();
                    $endDate = $today->copy()->month(6)->endOfMonth()->endOfDay();
                } else {
                    $startDate = $today->copy()->month(7)->startOfMonth();
                    $endDate = $today->copy()->endOfYear()->endOfDay();
                }
                break;
            case 'year':
                $startDate = $today->copy()->startOfYear();
                $endDate = $today->copy()->endOfYear()->endOfDay();
                break;
            case 'day':
            default:
                $startDate = $today->copy();
                $endDate = $today->copy()->endOfDay();
                break;
        }
        
        // 1. Collections in Period
        $collectionsPeriod = Payment::whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])->sum('amount');
        
        // Retain Daily Collection for the main card as a static reference
        $collectionsToday = Payment::whereDate('payment_date', $today->toDateString())->sum('amount');

        // 2. Overdue Installments
        $overdueCount = Installment::where('due_date', '<', $today)
            ->where('status', 'pending')
            ->count();
            
        $overdueAmount = Installment::where('due_date', '<', $today)
            ->where('status', 'pending')
            ->sum('amount');

        // 3. Portfolio Totals
        $totalCollected = Payment::sum('amount');
        $totalOutstanding = Loan::where('status', 'active')->sum('outstanding_balance');
        $totalPortfolio = $totalCollected + $totalOutstanding;

        $yieldInfo = DB::table('loans')
            ->selectRaw('SUM(amount) as principal, SUM(amount * interest_rate / 100) as expected_interest')
            ->where('status', 'active')
            ->first();

        // 4. Recent Activity (Filtered by period)
        $latestPayments = Payment::with(['loan.customer'])
            ->whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()->map(fn($p) => [
            'date' => Carbon::parse($p->payment_date)->format('d/m/Y'),
            'sort_date' => $p->payment_date,
            'type' => 'payment',
            'customer' => $p->loan->customer->name,
            'amount' => $p->amount
        ]);

        // 5. Portfolio Health
        $healthPercentage = 100;
        if ($totalPortfolio > 0) {
            $healthPercentage = max(0, (($totalPortfolio - $overdueAmount) / $totalPortfolio) * 100);
        }

        $latestLoans = Loan::with(['customer'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()->map(fn($l) => [
            'date' => Carbon::parse($l->created_at)->format('d/m/Y'),
            'sort_date' => $l->created_at->toDateTimeString(),
            'type' => 'loan',
            'customer' => $l->customer->name,
            'amount' => $l->amount
        ]);

        $recentActivity = $latestPayments->concat($latestLoans)->sortByDesc('sort_date')->values();

        return [
            'metrics' => [
                'collections_period' => (float)$collectionsPeriod,
                'collections_today' => (float)$collectionsToday,
                'overdue_count' => $overdueCount,
                'overdue_amount' => (float)$overdueAmount,
                'total_portfolio' => (float)$totalPortfolio,
                'total_collected' => (float)$totalCollected,
                'total_outstanding' => (float)$totalOutstanding,
                'expected_interest' => (float)($yieldInfo->expected_interest ?? 0),
                'upcoming_tomorrow_count' => Installment::where('due_date', $today->copy()->addDay()->toDateString())
                    ->where('status', 'pending')
                    ->count(),
                'upcoming_tomorrow_amount' => (float)Installment::where('due_date', $today->copy()->addDay()->toDateString())
                    ->where('status', 'pending')
                    ->sum('amount'),
                'active_loans_count' => Loan::where('status', 'active')->count(),
                'collection_health' => round($healthPercentage, 1),
            ],
            'recent_activity' => $recentActivity,
            'upcoming_installments' => Installment::with(['loan.customer'])
                ->where('due_date', $today->copy()->addDay()->toDateString())
                ->where('status', 'pending')
                ->get()->map(fn($i) => [
                    'id' => $i->id,
                    'customer' => $i->loan->customer->name,
                    'loan_id' => $i->loan->id,
                    'installment_number' => $i->installment_number,
                    'amount' => $i->amount,
                    'due_date' => Carbon::parse($i->due_date)->format('d/m/Y')
                ]),
            'period_details' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString()
            ]
        ];
    }
}
