<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $paginate = $request->integer('paginate', 10);

        $customers = Customer::query()
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(in_array($paginate, [10, 25, 50, 100], true) ? $paginate : 10)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['aktif', 'non_aktif'])],
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer berhasil ditambahkan.');
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['aktif', 'non_aktif'])],
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->update(['status' => 'non_aktif']);

        return redirect()->route('customers.index')->with('success', 'Customer berhasil dinonaktifkan.');
    }
}
