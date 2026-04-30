<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detalles del Préstamo #{{ $loan->id }}</title>
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
        }
        .summary-grid {
            width: 100%;
            margin-bottom: 30px;
        }
        .summary-grid td {
            padding: 5px 0;
            vertical-align: top;
        }
        .label {
            color: #64748b;
            font-weight: bold;
            width: 150px;
        }
        .value {
            color: #0f172a;
            font-weight: bold;
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
        .status-paid { color: #15803d; font-weight: bold; }
        .status-pending { color: #b45309; font-weight: bold; }
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
        .total-box {
            background-color: #0f172a;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .total-box .amount {
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    <h1 class="business-name">Sistema de Créditos SEFI</h1>
                    <span class="doc-type">Estado de Cuenta de Préstamo</span>
                </td>
                <td align="right">
                    <div style="font-weight: bold; font-size: 18px;">Folio #{{ $loan->id }}</div>
                    <div>Fecha: {{ date('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="total-box">
        <table width="100%">
            <tr>
                <td>
                    <div style="font-size: 10px; text-transform: uppercase; opacity: 0.7;">Saldo Restante por Pagar</div>
                    <div class="amount">${{ number_format($loan->outstanding_balance, 2) }}</div>
                </td>
                <td align="right">
                    <div style="font-size: 10px; text-transform: uppercase; opacity: 0.7;">Estatus del Crédito</div>
                    <div style="font-weight: bold; font-size: 16px;">{{ strtoupper($loan->status) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Información del Cliente</div>
    <table class="summary-grid">
        <tr>
            <td class="label">Nombre:</td>
            <td class="value">{{ $loan->customer->name }}</td>
            <td class="label">ID Cliente:</td>
            <td class="value">#{{ $loan->customer->id }}</td>
        </tr>
        <tr>
            <td class="label">Teléfono:</td>
            <td class="value">{{ $loan->customer->phone }}</td>
            <td class="label">Folio Pagaré:</td>
            <td class="value">{{ $loan->promissory_note_folio ?: 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Detalles del Crédito</div>
    <table class="summary-grid">
        <tr>
            <td class="label">Capital Entregado:</td>
            <td class="value">${{ number_format($loan->amount, 2) }}</td>
            <td class="label">Tasa de Interés:</td>
            <td class="value">{{ $loan->interest_rate }}% Global</td>
        </tr>
        <tr>
            <td class="label">Periodicidad:</td>
            <td class="value" style="text-transform: capitalize;">{{ $loan->periodicity }}</td>
            <td class="label">No. de Pagos:</td>
            <td class="value">{{ $loan->num_installments }} cuotas</td>
        </tr>
        <tr>
            <td class="label">Primer Pago:</td>
            <td class="value">{{ \Carbon\Carbon::parse($loan->first_payment_date)->format('d/m/Y') }}</td>
            <td class="label">Total a Pagar:</td>
            <td class="value">${{ number_format($loan->amount * (1 + $loan->interest_rate / 100), 2) }}</td>
        </tr>
    </table>

    <div class="section-title">Tabla de Amortización</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Fecha de Vencimiento</th>
                <th>Monto de Cuota</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loan->installments as $inst)
            <tr>
                <td align="center">{{ $inst->installment_number }}</td>
                <td>{{ \Carbon\Carbon::parse($inst->due_date)->format('d/m/Y') }}</td>
                <td>${{ number_format($inst->amount, 2) }}</td>
                <td>
                    <span class="{{ $inst->status === 'paid' ? 'status-paid' : 'status-pending' }}">
                        {{ $inst->status === 'paid' ? 'PAGADO' : 'PENDIENTE' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($loan->payments) > 0)
    <div class="section-title">Historial de Pagos Realizados</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID Pago</th>
                <th>Fecha de Pago</th>
                <th>Monto Recibido</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loan->payments as $payment)
            <tr>
                <td>#{{ $payment->id }}</td>
                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                <td class="status-paid">${{ number_format($payment->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        Documento generado automáticamente por Sistema de Créditos SEFI el {{ date('d/m/Y H:i') }}.
        <br>Este documento es para fines informativos y no sustituye al pagaré original.
    </div>
</body>
</html>
