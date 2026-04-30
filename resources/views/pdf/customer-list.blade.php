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
                    <span class="doc-type">Directorio de Clientes</span>
                </td>
                <td align="right">
                    <div>Fecha: {{ date('d/m/Y') }}</div>
                    <div>Total: {{ count($customers) }} registros</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Teléfono</th>
                <th>Fecha de Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td style="font-family: monospace; font-weight: bold; color: #64748b;">#{{ $customer->id }}</td>
                <td style="font-weight: bold; color: #0f172a;">{{ $customer->name }}</td>
                <td>{{ $customer->phone }}</td>
                <td>{{ $customer->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Documento generado automáticamente por Sistema de Créditos SEFI el {{ date('d/m/Y H:i') }}.
    </div>
</body>
</html>
