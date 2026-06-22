<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->when($request->filled('q'), fn ($q) =>
                $q->where('name', 'like', '%' . $request->string('q') . '%')
                  ->orWhere('phone', 'like', '%' . $request->string('q') . '%')
            )
            ->withCount('transactions')
            ->orderByDesc('total_spent')
            ->paginate(20)
            ->withQueryString();

        return view('customer.index', compact('customers'));
    }

    public function show(Customer $customer): View
    {
        $transactions = $customer->transactions()
            ->with('items', 'debt')
            ->latest()
            ->paginate(15);

        $debts = $customer->debts()
            ->with('payments', 'transaction')
            ->latest()
            ->get();

        $summary = [
            'total_spent'        => $customer->total_spent,
            'total_transactions' => $customer->transactions()->count(),
            'total_debt'         => $customer->total_debt,
            'last_visit'         => $customer->transactions()->latest()->value('created_at'),
        ];

        return view('customer.show', compact('customer', 'transactions', 'debts', 'summary'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'phone'   => ['nullable', 'string', 'max:20', 'unique:customers,phone'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes'   => ['nullable', 'string', 'max:500'],
        ]);

        $customer = Customer::create($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }

        return redirect()->route('customer.show', $customer)->with('status', 'Pelanggan berhasil ditambahkan.');
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'phone'   => ['nullable', 'string', 'max:20', "unique:customers,phone,{$customer->id}"],
            'address' => ['nullable', 'string', 'max:500'],
            'notes'   => ['nullable', 'string', 'max:500'],
        ]);

        $customer->update($data);

        return redirect()->route('customer.show', $customer)->with('status', 'Data pelanggan diperbarui.');
    }

    /** Cari pelanggan untuk autocomplete di POS (JSON) */
    public function search(Request $request)
    {
        $results = Customer::where('name', 'like', '%' . $request->string('q') . '%')
            ->orWhere('phone', 'like', '%' . $request->string('q') . '%')
            ->select('id', 'name', 'phone', 'total_debt')
            ->orderBy('name')
            ->limit(8)
            ->get();

        return response()->json($results);
    }
}
