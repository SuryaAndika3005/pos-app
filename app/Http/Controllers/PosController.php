<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    /** Tarif PPN. Idealnya pindahkan ke config/settings. */
    private const TAX_RATE = 0.11;

    /**
     * Tampilkan halaman kasir beserta daftar produk.
     */
public function index(Request $request)
    {
        // 1. Definisikan kategori material di sini
        $categories = [
            ['label' => 'Semua', 'icon' => '✨', 'value' => null],
            ['label' => 'Busa',  'icon' => '🧽', 'value' => 'Busa'],
            ['label' => 'Kain',  'icon' => '🧵', 'value' => 'Kain'],
            ['label' => 'Dakron','icon' => '☁️', 'value' => 'Dakron'],
        ];

        // 2. Ambil data produk
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

        // 3. Kirim $products DAN $categories ke tampilan
        return view('pos.index', compact('products', 'categories'));
    }

    /**
     * Proses checkout.
     *
     * Prinsip keamanan: payload dari klien HANYA dipercaya untuk id + qty.
     * Harga, subtotal, pajak, dan total DIHITUNG ULANG di server berdasarkan
     * data master produk. Seluruh proses dibungkus DB::transaction() agar
     * atomik (kalau ada satu langkah gagal, semua dibatalkan / rollback).
     */
    public function store(CheckoutRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $transaction = DB::transaction(function () use ($data) {

                $subtotal   = 0.0;
                $itemsToSave = [];

                foreach ($data['items'] as $line) {
                    // lockForUpdate mencegah race condition stok saat transaksi paralel.
                    $product = Product::lockForUpdate()->findOrFail($line['id']);

                    // Jaga presisi desimal — JANGAN dibulatkan ke bawah.
                    $qty = (float) $line['qty'];

                    // Produk bersatuan bulat tidak boleh dijual pecahan.
                    if ($product->unit_type === 'integer' && floor($qty) != $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Produk {$product->name} hanya bisa dibeli dalam jumlah bulat.",
                        ]);
                    }

                    if ((float) $product->stock < $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Stok {$product->name} tidak mencukupi (tersisa {$product->stock} {$product->unit}).",
                        ]);
                    }

                    $lineSubtotal = round((float) $product->price * $qty, 2);
                    $subtotal    += $lineSubtotal;

                    $itemsToSave[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,   // snapshot
                        'unit'         => $product->unit,
                        'price'        => $product->price,
                        'quantity'     => $qty,
                        'discount'     => 0,
                        'subtotal'     => $lineSubtotal,
                    ];

                    // Potong stok produk.
                    $product->decrement('stock', $qty);
                }

                $tax   = round($subtotal * self::TAX_RATE, 2);
                $total = round($subtotal + $tax, 2);

                $paid   = isset($data['paid_amount']) ? (float) $data['paid_amount'] : $total;
                $change = max(0, round($paid - $total, 2));

                $transaction = Transaction::create([
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'user_id'        => Auth::id(),
                    'subtotal'       => $subtotal,
                    'discount'       => 0,
                    'tax'            => $tax,
                    'total'          => $total,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'paid_amount'    => $paid,
                    'change_amount'  => $change,
                    'status'         => 'paid',
                    'note'           => $data['note'] ?? null,
                ]);

                $transaction->items()->createMany($itemsToSave);

                return $transaction->load('items');
            });
        } catch (ValidationException $e) {
            // Stok kurang / satuan salah → 422 dengan pesan jelas.
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success'        => true,
            'message'        => 'Transaksi berhasil disimpan.',
            'invoice_number' => $transaction->invoice_number,
            'transaction'    => $transaction,
        ]);
    }

    /**
     * Nomor invoice: INV-YYYYMMDD-XXXX (urut harian).
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';

        $lastNumber = Transaction::where('invoice_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $sequence = $lastNumber
            ? ((int) substr($lastNumber, -4)) + 1
            : 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
