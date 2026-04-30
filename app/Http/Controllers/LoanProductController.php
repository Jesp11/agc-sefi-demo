<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoanProductController extends Controller
{
    public function index()
    {
        return Inertia::render('LoanProducts/Index', [
            'products' => LoanProduct::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0',
            'frequency' => 'required|in:daily,weekly,fortnightly,monthly',
            'duration' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        LoanProduct::create($validated);

        return redirect()->route('loan-products.index');
    }

    public function update(Request $request, LoanProduct $loanProduct)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0',
            'frequency' => 'required|in:daily,weekly,fortnightly,monthly',
            'duration' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $loanProduct->update($validated);

        return redirect()->route('loan-products.index');
    }

    public function destroy(LoanProduct $loanProduct)
    {
        $loanProduct->delete();

        return redirect()->route('loan-products.index');
    }
}
