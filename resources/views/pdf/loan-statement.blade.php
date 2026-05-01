<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estado de Cuenta - {{ $loan->cliente->nombre }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            line-height: 1.4;
            font-size: 11px;
            margin: 0;
            padding: 30px;
        }
        .header {
            margin-bottom: 25px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 15px;
        }
        .business-name {
            font-size: 22px;
            font-weight: bold;
            margin: 0;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .doc-type {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: bold;
            margin-top: 2px;
        }
        .meta-right {
            text-align: right;
            font-size: 9px;
            color: #94a3b8;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .subject-header {
            margin-bottom: 25px;
            margin-top: 20px;
        }
        .subject-name {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .subject-sub {
            font-size: 9px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .kpi-container {
            width: 100%;
            margin-bottom: 30px;
        }
        .kpi-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
        }
        .kpi-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            border-left: 4px solid #0f172a;
            padding-left: 10px;
            margin-top: 30px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.data-table th {
            text-align: left;
            padding: 10px 8px;
            background-color: #f8fafc;
            color: #64748b;
            font-size: 8px;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
        }
        table.data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10px;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid { background-color: #dcfce7; color: #166534; }
        .status-partial { background-color: #fef9c3; color: #854d0e; }
        .status-pending { background-color: #fee2e2; color: #991b1b; }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    <h1 class="business-name">AGC Servicios Financieros</h1>
                    <div class="doc-type">Estado de Cuenta de Cliente</div>
                </td>
                <td class="meta-right">
                    SOPORTE AL CLIENTE<br>
                    <span style="color: #cbd5e1;">Generado: {{ date('d/m/Y H:i') }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="subject-header">
        <div class="subject-name">{{ $loan->cliente->nombre }}</div>
        <div class="subject-sub">REF. CRÉDITO #{{ $loan->id_credito }} | CICLO {{ $loan->ciclo }}</div>
    </div>

    <div class="kpi-container">
        <table width="100%" cellpadding="0" cellspacing="10">
            <tr>
                <td width="33%">
                    <div class="kpi-box">
                        <div class="kpi-label">Monto a Liquidar</div>
                        <div class="kpi-value">${{ number_format($loan->total, 2) }}</div>
                    </div>
                </td>
                <td width="33%">
                    <div class="kpi-box" style="border-color: #dcfce7; background-color: #f0fdf4;">
                        <div class="kpi-label" style="color: #10b981;">Total Pagado</div>
                        <div class="kpi-value" style="color: #059669;">${{ number_format($loan->pagos->sum('monto'), 2) }}</div>
                    </div>
                </td>
                <td width="33%">
                    <div class="kpi-box" style="border-color: #fee2e2; background-color: #fef2f2;">
                        <div class="kpi-label" style="color: #ef4444;">Saldo Restante</div>
                        <div class="kpi-value" style="color: #b91c1c;">${{ number_format($loan->total - $loan->pagos->sum('monto'), 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Tabla de Amortización</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="30">No.</th>
                <th>Fecha Límite</th>
                <th align="right">Cuota Sugerida</th>
                <th align="center">Estatus</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_paid = $loan->pagos->sum('monto');
                $remaining = $total_paid;
                $startDate = \Carbon\Carbon::parse($loan->fecha);
            @endphp
            @for($i = 1; $i <= $loan->plazo; $i++)
                @php
                    $paymentDate = $startDate->copy()->addWeeks($i);
                    $status = 'pending';
                    $statusText = 'Pendiente';

                    if ($remaining >= $loan->valor_ficha) {
                        $status = 'paid';
                        $statusText = 'Pagado';
                        $remaining -= $loan->valor_ficha;
                    } elseif ($remaining > 0) {
                        $status = 'partial';
                        $statusText = 'Abono: $' . number_format($remaining, 2);
                        $remaining = 0;
                    }
                @endphp
                <tr>
                    <td align="center"><b>{{ $i }}</b></td>
                    <td>{{ $paymentDate->format('d/m/Y') }}</td>
                    <td align="right"><b>${{ number_format($loan->valor_ficha, 2) }}</b></td>
                    <td align="center">
                        <span class="status-badge status-{{ $status }}">{{ $statusText }}</span>
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="section-title">Historial de Operaciones</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Fecha de Pago</th>
                <th>Concepto</th>
                <th align="right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loan->pagos->sortByDesc('fecha_pago')->take(10) as $pago)
            <tr>
                <td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y H:i') }}</td>
                <td>Abono realizado</td>
                <td align="right" style="color: #059669; font-weight: bold;">${{ number_format($pago->monto, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" align="center" style="padding: 20px; color: #94a3b8; font-style: italic;">Aún no se registran pagos.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <b>AGC Servicios Financieros - Creciendo Juntos</b>
        <br>Este documento es para fines informativos y soporte al cliente.
    </div>
</body>
</html>
