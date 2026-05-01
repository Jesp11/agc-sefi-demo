<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_credito' => 'required|exists:creditos,id_credito',
            'monto' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
        ]);

        return DB::transaction(function () use ($validated) {
            // Create payment record
            Pago::create($validated);

            return redirect()->back()->with('success', 'Pago registrado correctamente.');
        });
    }
}
