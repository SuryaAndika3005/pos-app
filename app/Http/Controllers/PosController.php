<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    private const TAX_RATE = 0.11;

    public function index(Request $request)
    {
        $categories = [
            ['label' => 'Semua', 'icon' => '✨', 'value' => null],
            ['label' => 'Busa',  'icon' => '🧽', 'value' => 'Busa'],
            ['label' => 'Kain',  'icon' => '🧵', 'value' => 'Kain'],
            ['label' => 'Dakron','icon' => '☁️', 'value' => 'Dakron'],
        ];

        $products = Product::query()
            ->where('is_active', true)
            ->when($request->filled('q'), fn ($q) =>
                $q->where('name', 'like', '%' . $request->string('q') . '%')
            )
            ->when($request->filled('category'), fn ($q) =>
                $q->where('category', $request->string('category'))
            )
            ->orderBy('name')
            ->get();

        // Antrean aktif: transaksi yang sudah dibayar tapi belum diserahkan
        $queue = Transaction::with('items')
            ->where('queue_status', 'paid')
            ->latest()
            ->get();

        return view('pos.index', compact('products', 'categories', 'queue'));
    }

    /**
     * Proses checkout.
     * Jika paid_amount < total → otomatis buat utang.
     */
    public function store(CheckoutRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $result = DB::transaction(function () use ($data) {

                $subtotal             = 0.0;
                $itemsToSave          = [];
                $stockMovementsToSave = [];

                foreach ($data['items'] as $line) {
                    $product = Product::lockForUpdate()->findOrFail($line['id']);
                    $qty     = (float) $line['qty'];

                    if ($product->unit_type === 'integer' && floor($qty) != $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Produk {$product->name} hanya bisa dijual dalam jumlah bulat.",
                        ]);
                    }
                    if ((float) $product->stock < $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Stok {$product->name} tidak mencukupi (tersisa {$product->stock} {$product->unit}).",
                        ]);
                    }

                    $lineSubtotal  = round((float) $product->price * $qty, 2);
                    $subtotal     += $lineSubtotal;

                    $itemsToSave[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'unit'         => $product->unit,
                        'price'        => $product->price,
                        'quantity'     => $qty,
                        'discount'     => 0,
                        'subtotal'     => $lineSubtotal,
                    ];

                    $stockBefore = (float) $product->stock;
                    $product->decrement('stock', $qty);

                    $stockMovementsToSave[] = [
                        'product_id'   => $product->id,
                        'stock_before' => $stockBefore,
                        'stock_after'  => $stockBefore - $qty,
                        'quantity'     => $qty,
                    ];
                }

                $tax    = round($subtotal * self::TAX_RATE, 2);
                $total  = round($subtotal + $tax, 2);
                $paid   = isset($data['paid_amount']) ? (float) $data['paid_amount'] : $total;
                $paid   = max(0, $paid);
                $change = max(0, round($paid - $total, 2));
                $debt   = round($total - min($paid, $total), 2);

                // ---- Resolve / buat Customer ----
                // FIX: Hanya cocokkan pelanggan jika customer_id dikirim (dipilih dari dropdown).
                // Jika nama diketik manual tanpa pilih dari dropdown, JANGAN cocokkan ke pelanggan lama
                // → transaksi tetap tercatat dengan nama teks saja, tanpa mengikat ke akun pelanggan manapun.
                $customer   = null;
                $customerId = null;

                if (! empty($data['customer_name'])) {
                    // Hanya ambil pelanggan jika kasir memilih dari direktori (ada customer_id)
                    if (! empty($data['customer_id'])) {
                        $customer = Customer::find($data['customer_id']);
                    }
                    // Tidak lagi auto-match berdasarkan nama — mencegah transaksi masuk ke pelanggan yang salah
                    // Tidak lagi auto-create pelanggan saat checkout — harus dibuat manual di direktori pelanggan
                    $customerId = $customer?->id;
                }

                $transaction = Transaction::create([
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'customer_name'  => $data['customer_name'] ?? null,
                    'customer_id'    => $customerId,
                    'user_id'        => Auth::id(),
                    'subtotal'       => $subtotal,
                    'discount'       => 0,
                    'tax'            => $tax,
                    'total'          => $total,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'paid_amount'    => $paid,
                    'change_amount'  => $change,
                    'status'         => 'paid',
                    'queue_status'   => 'paid',
                    'note'           => $data['note'] ?? null,
                ]);

                $transaction->items()->createMany($itemsToSave);

                foreach ($stockMovementsToSave as $movement) {
                    StockMovement::create([
                        'product_id'     => $movement['product_id'],
                        'user_id'        => Auth::id(),
                        'type'           => 'out',
                        'quantity'       => $movement['quantity'],
                        'stock_before'   => $movement['stock_before'],
                        'stock_after'    => $movement['stock_after'],
                        'source'         => 'sale',
                        'transaction_id' => $transaction->id,
                    ]);
                }

                // ---- Auto-buat Utang jika kurang bayar ----
                $debtRecord = null;
                if ($debt > 0 && $customer) {
                    $debtRecord = Debt::create([
                        'customer_id'      => $customer->id,
                        'transaction_id'   => $transaction->id,
                        'created_by'       => Auth::id(),
                        'original_amount'  => $debt,
                        'paid_amount'      => 0,
                        'remaining_amount' => $debt,
                        'status'           => 'open',
                        'note'             => "Kurang bayar dari {$transaction->invoice_number}",
                        'due_date'         => $data['due_date'] ?? null,
                    ]);

                    $customer->increment('total_debt', $debt);
                } elseif ($debt > 0) {
                    throw ValidationException::withMessages([
                        'customer_name' => 'Pilih pelanggan terdaftar (dari dropdown) jika pembayaran kurang dari total.',
                    ]);
                }

                if ($customer) {
                    $customer->increment('total_spent', $total);
                }

                return [
                    'transaction' => $transaction->load('items'),
                    'debt'        => $debtRecord,
                    'debt_amount' => $debt,
                ];
            });
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success'        => true,
            'message'        => 'Transaksi berhasil disimpan.',
            'invoice_number' => $result['transaction']->invoice_number,
            'transaction'    => $result['transaction'],
            'debt_amount'    => $result['debt_amount'],
            'has_debt'       => $result['debt_amount'] > 0,
        ]);
    }

    /**
     * Tandai transaksi sebagai "Barang Sudah Diserahkan" (completed).
     */
    public function complete(Transaction $transaction): JsonResponse
    {
        if ($transaction->queue_status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Sudah selesai.'], 422);
        }

        $transaction->update(['queue_status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => "#{$transaction->invoice_number} sudah diserahkan.",
        ]);
    }

    /**
     * Tampilkan form edit nota transaksi.
     */
    public function edit(Transaction $transaction)
    {
        // Hanya transaksi berstatus 'paid' (belum void) yang bisa diedit
        if ($transaction->status === 'void') {
            return redirect()->route('report.show', $transaction)
                ->with('status', 'Transaksi yang sudah dibatalkan tidak bisa diedit.');
        }

        $transaction->load('items');
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('pos.edit', compact('transaction', 'products'));
    }

    /**
     * Simpan perubahan nota transaksi.
     * Mengembalikan stok item yang dihapus/dikurangi, mengurangi stok item yang ditambah.
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $data = $request->validate([
            'items'                => ['required', 'array', 'min:1'],
            'items.*.id'           => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'          => ['required', 'numeric', 'gt:0'],
            'customer_name'        => ['nullable', 'string', 'max:100'],
            'customer_id'          => ['nullable', 'integer', 'exists:customers,id'],
            'payment_method'       => ['nullable', 'in:cash,qris,transfer,debit'],
            'paid_amount'          => ['nullable', 'numeric', 'min:0'],
            'note'                 => ['nullable', 'string', 'max:500'],
        ]);

        if ($transaction->status === 'void') {
            return response()->json(['success' => false, 'message' => 'Transaksi void tidak bisa diedit.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($data, $transaction) {

                // --- Kembalikan stok item lama ---
                foreach ($transaction->items as $oldItem) {
                    if ($oldItem->product_id) {
                        $product = Product::lockForUpdate()->find($oldItem->product_id);
                        if ($product) {
                            $stockBefore = (float) $product->stock;
                            $product->increment('stock', (float) $oldItem->quantity);
                            StockMovement::create([
                                'product_id'     => $product->id,
                                'user_id'        => Auth::id(),
                                'type'           => 'in',
                                'quantity'       => (float) $oldItem->quantity,
                                'stock_before'   => $stockBefore,
                                'stock_after'    => $stockBefore + (float) $oldItem->quantity,
                                'source'         => 'edit_reversal',
                                'transaction_id' => $transaction->id,
                                'note'           => 'Koreksi edit nota ' . $transaction->invoice_number,
                            ]);
                        }
                    }
                }

                // Hapus item lama
                $transaction->items()->delete();

                // --- Proses item baru ---
                $subtotal    = 0.0;
                $itemsToSave = [];

                foreach ($data['items'] as $line) {
                    $product = Product::lockForUpdate()->findOrFail($line['id']);
                    $qty     = (float) $line['qty'];

                    if ($product->unit_type === 'integer' && floor($qty) != $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Produk {$product->name} hanya bisa dijual dalam jumlah bulat.",
                        ]);
                    }
                    if ((float) $product->stock < $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Stok {$product->name} tidak mencukupi (tersisa {$product->stock} {$product->unit}).",
                        ]);
                    }

                    $lineSubtotal  = round((float) $product->price * $qty, 2);
                    $subtotal     += $lineSubtotal;

                    $itemsToSave[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'unit'         => $product->unit,
                        'price'        => $product->price,
                        'quantity'     => $qty,
                        'discount'     => 0,
                        'subtotal'     => $lineSubtotal,
                    ];

                    $stockBefore = (float) $product->stock;
                    $product->decrement('stock', $qty);

                    StockMovement::create([
                        'product_id'     => $product->id,
                        'user_id'        => Auth::id(),
                        'type'           => 'out',
                        'quantity'       => $qty,
                        'stock_before'   => $stockBefore,
                        'stock_after'    => $stockBefore - $qty,
                        'source'         => 'edit_sale',
                        'transaction_id' => $transaction->id,
                        'note'           => 'Item dari edit nota ' . $transaction->invoice_number,
                    ]);
                }

                $tax    = round($subtotal * self::TAX_RATE, 2);
                $total  = round($subtotal + $tax, 2);
                $paid   = isset($data['paid_amount']) ? (float) $data['paid_amount'] : $total;
                $paid   = max(0, $paid);
                $change = max(0, round($paid - $total, 2));

                // Resolve customer (sama dengan store: hanya jika customer_id ada)
                $customer   = null;
                $customerId = null;
                if (! empty($data['customer_name']) && ! empty($data['customer_id'])) {
                    $customer   = Customer::find($data['customer_id']);
                    $customerId = $customer?->id;
                } elseif (! empty($data['customer_name'])) {
                    // Nama diketik manual, tidak ikat ke customer
                    $customerId = $transaction->customer_id; // pertahankan link lama jika ada
                    $customer   = $transaction->customer_id ? Customer::find($transaction->customer_id) : null;
                }

                $transaction->update([
                    'customer_name'  => $data['customer_name'] ?? null,
                    'customer_id'    => $customerId,
                    'subtotal'       => $subtotal,
                    'tax'            => $tax,
                    'total'          => $total,
                    'payment_method' => $data['payment_method'] ?? $transaction->payment_method,
                    'paid_amount'    => $paid,
                    'change_amount'  => $change,
                    'note'           => $data['note'] ?? null,
                ]);

                $transaction->items()->createMany($itemsToSave);

                // Sinkron ulang cache total_spent pelanggan jika ada
                if ($customer) {
                    $customer->recalculateSpent();
                }

                return $transaction->load('items');
            });
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Nota berhasil diperbarui.',
            'transaction' => $result,
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';
        $lastNumber = Transaction::where('invoice_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');
        $sequence = $lastNumber ? ((int) substr($lastNumber, -4)) + 1 : 1;
        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}