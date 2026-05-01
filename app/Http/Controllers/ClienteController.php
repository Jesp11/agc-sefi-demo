<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Direccion;
use App\Models\Referencia;
use App\Models\Aval;
use App\Models\Asesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ClienteController extends Controller
{
    public function index()
    {
        return Inertia::render('Customers/Index', [
            'customers' => Cliente::with(['direcciones', 'referencias', 'avales', 'creditos', 'asesor'])->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Customers/Create', [
            'asesores' => Asesor::orderBy('nombre')->get()
        ]);
    }

    public function show(Cliente $customer)
    {
        return Inertia::render('Customers/Show', [
            'customer' => $customer->load(['direcciones', 'referencias', 'avales', 'creditos.asesor', 'asesor'])
        ]);
    }

    public function exportPdf()
    {
        $customers = Cliente::with('creditos')->orderBy('nombre')->get();
        $pdf = Pdf::loadView('pdf.customer-list', compact('customers'));
        return $pdf->download('Directorio_Clientes.pdf');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Cliente
            'nombre' => 'required|string|max:255',
            'id_asesor' => 'nullable|exists:asesores,id_asesor',
            'curp' => 'nullable|string|max:50',
            'clave_elector' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'ocupacion' => 'nullable|string|max:100',
            
            // Direcciones
            'direcciones' => 'nullable|array',
            'direcciones.*.direccion' => 'required|string',
            'direcciones.*.entre_calles' => 'nullable|string',
            'direcciones.*.tipo' => 'required|in:casa,trabajo',

            // Referencias
            'referencias' => 'nullable|array',
            'referencias.*.nombre' => 'required|string|max:255',
            'referencias.*.tipo' => 'required|in:familiar,amistad',
            'referencias.*.parentesco' => 'nullable|string|max:100',
            'referencias.*.telefono' => 'nullable|string|max:20',
            'referencias.*.direccion' => 'nullable|string',
            'referencias.*.anios_relacion' => 'nullable|integer',

            // Avales
            'avales' => 'nullable|array',
            'avales.*.nombre' => 'required|string|max:255',
            'avales.*.telefono' => 'nullable|string|max:20',
            'avales.*.direccion' => 'nullable|string',
            'avales.*.parentesco' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($validated) {
            $cliente = Cliente::create([
                'nombre' => $validated['nombre'],
                'id_asesor' => $validated['id_asesor'],
                'curp' => $validated['curp'],
                'clave_elector' => $validated['clave_elector'],
                'telefono' => $validated['telefono'],
                'ocupacion' => $validated['ocupacion'],
            ]);

            if (!empty($validated['direcciones'])) {
                foreach ($validated['direcciones'] as $dirData) {
                    $direccion = Direccion::create([
                        'direccion' => $dirData['direccion'],
                        'entre_calles' => $dirData['entre_calles'],
                    ]);
                    $cliente->direcciones()->attach($direccion->id_direccion, ['tipo' => $dirData['tipo']]);
                }
            }

            if (!empty($validated['referencias'])) {
                foreach ($validated['referencias'] as $refData) {
                    $cliente->referencias()->create($refData);
                }
            }

            if (!empty($validated['avales'])) {
                foreach ($validated['avales'] as $avalData) {
                    $cliente->avales()->create($avalData);
                }
            }
        });

        return redirect()->route('customers.index')->with('success', 'Cliente creado exitosamente.');
    }

    public function edit(Cliente $customer)
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer->load(['direcciones', 'referencias', 'avales']),
            'asesores' => Asesor::orderBy('nombre')->get()
        ]);
    }

    public function update(Request $request, Cliente $customer)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'id_asesor' => 'nullable|exists:asesores,id_asesor',
            'curp' => 'nullable|string|max:50',
            'clave_elector' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'ocupacion' => 'nullable|string|max:100',
            
            'direcciones' => 'nullable|array',
            'direcciones.*.id_direccion' => 'nullable|exists:direcciones,id_direccion',
            'direcciones.*.direccion' => 'required|string',
            'direcciones.*.entre_calles' => 'nullable|string',
            'direcciones.*.pivot.tipo' => 'required|in:casa,trabajo',

            'referencias' => 'nullable|array',
            'referencias.*.id_referencia' => 'nullable|exists:referencias,id_referencia',
            'referencias.*.nombre' => 'required|string|max:255',
            'referencias.*.tipo' => 'required|in:familiar,amistad',
            'referencias.*.parentesco' => 'nullable|string|max:100',
            'referencias.*.telefono' => 'nullable|string|max:20',
            'referencias.*.direccion' => 'nullable|string',
            'referencias.*.anios_relacion' => 'nullable|integer',

            'avales' => 'nullable|array',
            'avales.*.id_aval' => 'nullable|exists:avales,id_aval',
            'avales.*.nombre' => 'required|string|max:255',
            'avales.*.telefono' => 'nullable|string|max:20',
            'avales.*.direccion' => 'nullable|string',
            'avales.*.parentesco' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($validated, $customer) {
            $customer->update([
                'nombre' => $validated['nombre'],
                'id_asesor' => $validated['id_asesor'],
                'curp' => $validated['curp'],
                'clave_elector' => $validated['clave_elector'],
                'telefono' => $validated['telefono'],
                'ocupacion' => $validated['ocupacion'],
            ]);

            // Sync Direcciones
            if (isset($validated['direcciones'])) {
                foreach ($validated['direcciones'] as $dirData) {
                    if (isset($dirData['id_direccion'])) {
                        $direccion = Direccion::find($dirData['id_direccion']);
                        $direccion->update([
                            'direccion' => $dirData['direccion'],
                            'entre_calles' => $dirData['entre_calles'],
                        ]);
                    } else {
                        $direccion = Direccion::create([
                            'direccion' => $dirData['direccion'],
                            'entre_calles' => $dirData['entre_calles'],
                        ]);
                        $customer->direcciones()->attach($direccion->id_direccion, ['tipo' => $dirData['pivot']['tipo']]);
                    }
                }
            }

            // Sync Referencias
            if (isset($validated['referencias'])) {
                $refIds = [];
                foreach ($validated['referencias'] as $refData) {
                    if (isset($refData['id_referencia'])) {
                        $referencia = Referencia::find($refData['id_referencia']);
                        $referencia->update($refData);
                        $refIds[] = $refData['id_referencia'];
                    } else {
                        $referencia = $customer->referencias()->create($refData);
                        $refIds[] = $referencia->id_referencia;
                    }
                }
                $customer->referencias()->whereNotIn('id_referencia', $refIds)->delete();
            }

            // Sync Avales
            if (isset($validated['avales'])) {
                $avalIds = [];
                foreach ($validated['avales'] as $avalData) {
                    if (isset($avalData['id_aval'])) {
                        $aval = Aval::find($avalData['id_aval']);
                        $aval->update($avalData);
                        $avalIds[] = $avalData['id_aval'];
                    } else {
                        $aval = $customer->avales()->create($avalData);
                        $avalIds[] = $aval->id_aval;
                    }
                }
                $customer->avales()->whereNotIn('id_aval', $avalIds)->delete();
            }
        });

        return redirect()->route('customers.show', $customer->id_cliente)->with('success', 'Expediente actualizado exitosamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->back()->with('success', 'Cliente eliminado exitosamente.');
    }
}
