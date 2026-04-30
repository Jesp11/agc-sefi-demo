<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estado de Operación</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #334155;
            line-height: 1.5;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .header {
            padding: 20px 0;
            border-bottom: 2px solid #0d9488;
            margin-bottom: 30px;
        }
        .business-name {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }
        .doc-type {
            font-size: 14px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 15px;
            border-left: 4px solid #0d9488;
            padding-left: 10px;
            margin-top: 30px;
        }
        .metrics-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .metric-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .metric-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 5px;
        }
        .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.data-table th {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
        }
        table.data-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            padding: 20px 0;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%">
            <tr>
                @php
                    $periodLabels = [
                        'day' => 'Diario',
                        'week' => 'Semanal',
                        'fortnight' => 'Quincenal',
                        'month' => 'Mensual',
                        'custom' => 'Personalizado'
                    ];
                    $periodLabel = $periodLabels[$current_period ?? 'month'] ?? 'Mensual';
                    $startDate = \Carbon\Carbon::parse($period_details['start'])->format('d/m/Y');
                    $endDate = \Carbon\Carbon::parse($period_details['end'])->format('d/m/Y');
                @endphp
                <td>
                    <h1 class="business-name">Sistema de Créditos SEFI</h1>
                    <span class="doc-type">Reporte de Operación {{ $periodLabel }}</span><br>
                    <span style="font-size: 11px; color: #94a3b8;">Periodo: {{ $startDate }} al {{ $endDate }}</span>
                </td>
                <td align="right">
                    <div>Fecha Impresión: {{ date('d/m/Y') }}</div>
                    <div>Hora: {{ date('H:i') }}</div>

                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Resumen Financiero</div>
    <table class="metrics-grid">
        <tr>
            <td width="33%">
                <div class="metric-card">
                    <div class="metric-label">Cartera Activa</div>
                    <div class="metric-value">${{ number_format($metrics['total_portfolio'], 2) }}</div>
                </div>
            </td>
            <td width="33%">
                <div class="metric-card">
                    <div class="metric-label">Cobrado en Periodo</div>
                    <div class="metric-value" style="color: #0d9488;">${{ number_format($metrics['collections_period'], 2) }}</div>
                </div>
            </td>
            <td width="33%">
                <div class="metric-card">
                    <div class="metric-label">Saldo Pendiente</div>
                    <div class="metric-value" style="color: #be123c;">${{ number_format($metrics['total_outstanding'], 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Actividad Reciente</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recent_activity as $activity)
            <tr>
                <td>{{ \Carbon\Carbon::parse($activity['sort_date'])->format('d/m/Y H:i') }}</td>
                <td>
                    <span style="font-weight: bold; color: #0f172a;">{{ $activity['type'] === 'loan' ? 'Préstamo Entregado' : 'Abono Recibido' }}</span>
                    <br>
                    <small style="color: #64748b;">Cliente: {{ $activity['customer'] }}</small>
                </td>
                <td style="font-weight: bold; color: {{ $activity['type'] === 'loan' ? '#be123c' : '#0d9488' }};">
                    {{ $activity['type'] === 'loan' ? '-' : '+' }} ${{ number_format($activity['amount'], 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Documento generado automáticamente por Sistema de Créditos SEFI el {{ date('d/m/Y H:i') }}.
    </div>
</body>
</html>
