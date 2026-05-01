<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resumen de Ahorro Voluntario {{ $year }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            line-height: 1.2;
            font-size: 8px;
            margin: 0;
            padding: 30px;
        }
        .header {
            margin-bottom: 25px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 15px;
        }
        .business-name {
            font-size: 24px;
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
            margin-bottom: 20px;
        }
        table.data-table th {
            text-align: center;
            padding: 10px 8px;
            background-color: #f8fafc;
            color: #64748b;
            font-size: 7px;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
        }
        table.data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f1f5f9;
            text-align: right;
            font-size: 9px;
        }
        .employee-name {
            text-align: left;
            font-weight: bold;
            color: #0f172a;
        }
        .total-col {
            background-color: #f8fafc;
            font-weight: bold;
            color: #0f172a;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
        }
        tfoot {
            background-color: #f8fafc;
            color: #1e293b;
            border-top: 2px solid #e2e8f0;
        }
        tfoot td {
            padding: 10px 8px;
            font-weight: bold;
            border: none;
        }
    </style>
</head>
<body>
    @php
        $monthSums = array_fill(1, 12, 0);
        foreach($report as $row) {
            foreach($row['months'] as $m => $val) {
                $monthSums[$m] += $val;
            }
        }
        $grandTotal = array_sum($monthSums);
    @endphp

    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    <h1 class="business-name">AGC Servicios Financieros</h1>
                    <div class="doc-type">Resumen Anual de Ahorro Voluntario</div>
                </td>
                <td class="meta-right">
                    GESTIÓN DE PERSONAL<br>
                    <span style="color: #cbd5e1;">AÑO FISCAL: {{ $year }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="subject-header">
        <div class="subject-name">Ahorro Voluntario del Personal</div>
        <div class="subject-sub">CORRESPONDIENTE AL CICLO {{ $year }} | GENERADO: {{ date('d/m/Y') }}</div>
    </div>

    <div class="kpi-container">
        <div class="kpi-box" style="width: 250px;">
            <div class="kpi-label">Fondo de Ahorro Total Acumulado</div>
            <div class="kpi-value" style="color: #6366f1;">${{ number_format($grandTotal, 2) }}</div>
        </div>
    </div>

    <div class="section-title">Distribución de Aportaciones</div>
    <table class="data-table">
        <thead>
            <tr>
                <th align="left" width="120">EMPLEADO / ASESOR</th>
                <th width="30">ID</th>
                @for($m=1; $m<=12; $m++)
                    <th>{{ ['','ENE','FEB','MZO','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'][$m] }}/{{ substr($year, 2) }}</th>
                @endfor
                <th width="60">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report as $row)
            <tr>
                <td class="employee-name">{{ $row['nombre'] }}</td>
                <td align="center" style="color: #94a3b8;">#{{ $row['id'] }}</td>
                @foreach($row['months'] as $amount)
                    <td style="color: {{ $amount > 0 ? '#6366f1' : '#e2e8f0' }}; font-weight: {{ $amount > 0 ? 'bold' : 'normal' }};">
                        {{ $amount > 0 ? number_format($amount, 0) : '-' }}
                    </td>
                @endforeach
                <td class="total-col" style="color: #0f172a;">${{ number_format($row['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: left; padding-left: 10px;">SUMATORIA MENSUAL</td>
                @foreach($monthSums as $sum)
                    <td align="right">{{ $sum > 0 ? number_format($sum, 0) : '-' }}</td>
                @endforeach
                <td align="right" style="background-color: #f1f5f9; color: #6366f1;">
                    ${{ number_format($grandTotal, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <b>CONTROL OFICIAL - AGC Servicios Financieros</b>
        <br>Documento generado por el sistema de administración de personal.
    </div>
</body>
</html>
