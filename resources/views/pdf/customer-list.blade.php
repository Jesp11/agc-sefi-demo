<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Directorio de Clientes</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #334155;
            line-height: 1.5;
            font-size: 11px;
            margin: 0;
            padding: 40px;
        }
        .header {
            padding: 10px 0;
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
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
        }
        table.data-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10px;
        }
        .footer {
            position: fixed;
            bottom: 30px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    <h1 class="business-name">AGC Servicios Financieros</h1>
                    <span class="doc-type">Directorio de Clientes</span>
                </td>
                <td align="right" style="font-size: 10px; color: #64748b;">
                    <div>Fecha: {{ date('d/m/Y') }}</div>
                    <div>Registros: {{ count($customers) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="30">ID</th>
                <th>Nombre del Cliente</th>
                <th>Domicilio Principal</th>
                <th width="80">Teléfono</th>
                <th width="40" align="center">Ciclo</th>
                <th width="70">Ocupación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td style="font-family: monospace; color: #94a3b8;">#{{ $customer->id_cliente }}</td>
                <td style="font-weight: bold; color: #0f172a;">{{ $customer->nombre }}</td>
                <td style="font-size: 9px;">
                    {{ $customer->direcciones->firstWhere('pivot.tipo', 'casa')->direccion ?? '---' }}
                </td>
                <td style="font-weight: bold;">{{ $customer->telefono ?: '---' }}</td>
                <td align="center" style="font-weight: bold; color: #0f172a;">
                    {{ $customer->creditos->max('ciclo') ?? 0 }}
                </td>
                <td style="color: #64748b; font-style: italic;">{{ $customer->ocupacion ?: '---' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Confidencial - Uso interno exclusivo de AGC Servicios Financieros. Generado el {{ date('d/m/Y H:i') }}.
    </div>
</body>
</html>
