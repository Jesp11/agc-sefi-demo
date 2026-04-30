<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pagos Proyectados</title>
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
            border-bottom: 2px solid #4f46e5;
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
            border-left: 4px solid #4f46e5;
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
            color: #4f46e5;
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
                <td>
                    <h1 class="business-name">Sistema de Créditos SEFI</h1>
                    <span class="doc-type">Reporte de Pagos Proyectados</span><br>
                    <span style="font-size: 11px; color: #94a3b8;">Fecha de Proyección: {{ $date }}</span>
                </td>
                <td align="right">
                    <div>Fecha Impresión: {{ date('d/m/Y') }}</div>
                    <div>Hora: {{ date('H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="metrics-grid">
        <tr>
            <td width="50%">
                <div class="metric-card">
                    <div class="metric-label">Total de Pagos Esperados</div>
                    <div class="metric-value" style="color: #4f46e5;">{{ $installments->count() }} cuotas</div>
                </div>
            </td>
            <td width="50%">
                <div class="metric-card">
                    <div class="metric-label">Monto Proyectado a Recibir</div>
                    <div class="metric-value" style="color: #10b981;">${{ number_format($total, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Detalle de Cuotas Programadas</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Préstamo Folio</th>
                <th>No. Cuota</th>
                <th style="text-align: right;">Monto a Cobrar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($installments as $installment)
            <tr>
                <td>
                    <span style="font-weight: bold; color: #0f172a;">{{ $installment->loan->customer->name }}</span>
                    <br>
                    <small style="color: #64748b;">Tel: {{ $installment->loan->customer->phone ?? 'N/D' }}</small>
                </td>
                <td>#{{ str_pad($installment->loan->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $installment->installment_number }}</td>
                <td style="font-weight: bold; color: #10b981; text-align: right;">
                    ${{ number_format($installment->amount, 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">
                    No hay pagos proyectados para esta fecha.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Documento generado automáticamente por Sistema de Créditos SEFI el {{ date('d/m/Y H:i') }}.
    </div>
</body>
</html>
