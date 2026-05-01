<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cartera Grupal</title>
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
        .group-header-row {
            background-color: #f1f5f9;
            border-top: 2px solid #e2e8f0;
        }
        .group-name {
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 12px !important;
            font-size: 10px;
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
                    <div class="doc-type">Reporte de Cartera Grupal</div>
                </td>
                <td class="meta-right">
                    DOCUMENTO OPERATIVO<br>
                    <span style="color: #cbd5e1;">Generado: {{ date('d/m/Y H:i') }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="subject-header">
        <div class="subject-name">Acreditados - Cartera Grupal</div>
        <div class="subject-sub">TOTAL DE REGISTROS: {{ count($customers) }} CLIENTES ASIGNADOS A GRUPO</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="30">ID</th>
                <th>Nombre del Cliente</th>
                <th width="100">CURP</th>
                <th>Domicilio</th>
                <th width="80">Teléfono</th>
                <th width="40" align="center">Ciclo</th>
                <th width="100">Asesor Responsable</th>
            </tr>
        </thead>
        <tbody>
            @php $currentGroupId = null; @endphp
            @foreach($customers as $customer)
                @if($currentGroupId !== $customer->id_grupo)
                    <tr class="group-header-row">
                        <td colspan="7" class="group-name">
                            GRUPO: {{ $customer->grupo ? $customer->grupo->nombre : 'SIN GRUPO ASIGNADO' }}
                        </td>
                    </tr>
                    @php $currentGroupId = $customer->id_grupo; @endphp
                @endif
                <tr>
                    <td style="color: #94a3b8;">#{{ $customer->id_cliente }}</td>
                    <td style="font-weight: bold; color: #0f172a;">{{ $customer->nombre }}</td>
                    <td style="font-family: monospace; font-size: 8px;">{{ $customer->curp ?: '---' }}</td>
                    <td style="font-size: 9px; color: #64748b;">
                        {{ $customer->direcciones->firstWhere('pivot.tipo', 'casa')->direccion ?? 'Sin dirección' }}
                    </td>
                    <td style="font-weight: bold;">{{ $customer->telefono ?: '---' }}</td>
                    <td align="center" style="font-weight: bold; color: #6366f1;">
                        {{ $customer->creditos->max('ciclo') ?? 0 }}
                    </td>
                    <td style="font-size: 9px; font-weight: bold;">{{ $customer->asesor ? $customer->asesor->nombre : 'GENERAL' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <b>CONFIDENCIAL - AGC Servicios Financieros</b>
        <br>Este documento es para uso interno exclusivo de la administración.
    </div>
</body>
</html>
