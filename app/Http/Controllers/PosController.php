<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
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
                $debt   = round($total - min($paid, $total), 2); // selisih kurang bayar

                // ---- Resolve / buat Customer ----
                $customer   = null;
                $customerId = null;

                if (! empty($data['customer_name'])) {
                    // Cari berdasarkan customer_id yang dikirim (kalau dipilih dari direktori)
                    if (! empty($data['customer_id'])) {
                        $customer = Customer::find($data['customer_id']);
                    }
                    // Kalau tidak ada, cari by nama (case-insensitive)
                    if (! $customer) {
                        $customer = Customer::whereRaw('LOWER(name) = ?', [strtolower(trim($data['customer_name']))])->first();
                    }
                    // Kalau masih tidak ada, buat baru
                    if (! $customer) {
                        $customer = Customer::create(['name' => trim($data['customer_name'])]);
                    }
                    $customerId = $customer->id;
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
                    'queue_status'   => 'paid',   // masuk antrean aktif
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

                    // Update cache total_debt di customer
                    $customer->increment('total_debt', $debt);
                } elseif ($debt > 0) {
                    // Kurang bayar tapi tidak ada nama pelanggan → tolak
                    throw ValidationException::withMessages([
                        'customer_name' => 'Nama pelanggan wajib diisi jika pembayaran kurang dari total.',
                    ]);
                }

                // Update cache total_spent customer
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
