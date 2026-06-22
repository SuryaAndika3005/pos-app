<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Debt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DebtController extends Controller
{
    /** Daftar semua utang aktif (lintas pelanggan) */
    public function index(Request $request): View
    {
        $debts = Debt::with('customer', 'transaction', 'createdBy')
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->string('status'))
            )
            ->when(! $request->filled('status'), fn ($q) =>
                $q->whereIn('status', ['open', 'partial'])  // default: hanya aktif
            )
            ->when($request->filled('q'), fn ($q) =>
                $q->whereHas('customer', fn ($cq) =>
                    $cq->where('name', 'like', '%' . $request->string('q') . '%')
                )
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total_open'    => Debt::where('status', 'open')->sum('remaining_amount'),
            'total_partial' => Debt::where('status', 'partial')->sum('remaining_amount'),
            'total_active'  => Debt::whereIn('status', ['open', 'partial'])->sum('remaining_amount'),
            'count_overdue' => Debt::whereIn('status', ['open', 'partial'])
                                   ->whereNotNull('due_date')
                                   ->whereDate('due_date', '<', now())
                                   ->count(),
        ];

        return view('debt.index', compact('debts', 'summary'));
    }

    /** Detail satu utang + riwayat pembayarannya */
    public function show(Debt $debt): View
    {
        $debt->load('customer', 'transaction.items', 'payments.receivedBy', 'createdBy');
        return view('debt.show', compact('debt'));
    }

    /**
     * Proses pembayaran utang (bisa cicilan).
     * Bisa dipanggil dari halaman debt maupun dari profil customer.
     */
    public function pay(Request $request, Debt $debt): RedirectResponse|JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', "max:{$debt->remaining_amount}"],
            'method' => ['required', 'in:cash,qris,transfer,debit'],
            'note'   => ['nullable', 'string', 'max:255'],
        ], [
            'amount.max' => "Pembayaran tidak boleh melebihi sisa utang (Rp " . number_format($debt->remaining_amount, 0, ',', '.') . ").",
        ]);

        DB::transaction(function () use ($request, $debt) {
            $debt->receivePayment(
                amount:     (float) $request->input('amount'),
                method:     $request->input('method'),
                note:       $request->input('note'),
                receivedBy: Auth::id(),
            );
        });

        $message = $debt->fresh()->status === 'paid'
            ? "Utang {$debt->customer->name} lunas!"
            : "Pembayaran Rp " . number_format($request->input('amount'), 0, ',', '.') . " diterima.";

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'debt' => $debt->fresh()]);
        }

        return back()->with('status', $message);
    }

    /**
     * Lunasi semua utang satu pelanggan sekaligus.
     */
    public function payAll(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'in:cash,qris,transfer,debit'],
            'note'   => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $customer) {
            $customer->activeDebts()->each(function (Debt $debt) use ($request) {
                $debt->receivePayment(
                    amount:     (float) $debt->remaining_amount,
                    method:     $request->input('method'),
                    note:       $request->input('note') ?? 'Pelunasan semua utang',
                    receivedBy: Auth::id(),
                );
            });
        });

        return redirect()->route('customer.show', $customer)
            ->with('status', "Semua utang {$customer->name} telah dilunasi.");
    }
}
