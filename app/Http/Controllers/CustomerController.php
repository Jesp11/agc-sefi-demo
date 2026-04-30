<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerController extends Controller
{
    public function index()
    {
        return Inertia::render('Customers/Index', [
            'customers' => Customer::all()
        ]);
    }

    public function exportPdf()
    {
        $customers = Customer::orderBy('name')->get();
        $pdf = Pdf::loadView('pdf.customer-list', compact('customers'));
        return $pdf->download('Directorio_Clientes.pdf');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        Customer::create($validated);

        return redirect()->back()->with('success', 'Cliente creado exitosamente.');
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $customer->update($validated);

        return redirect()->back()->with('success', 'Cliente actualizado exitosamente.');
    }
}
