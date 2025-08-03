<?php

namespace App\Http\Controllers;

use App\Models\Payments;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use App\Http\Resources\PaymentResource;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Menampilkan semua data pembayaran.
     */
    public function index() {
        $payments = Payments::with('orderItems')->get();

        if ($payments->isEmpty()) {
            return response()->json([
                'message' => 'No Payments Found!'
            ], 404);
        }

        return response()->json(PaymentResource::collection($payments), 200);
    }

    /**
     * Menampilkan detail pembayaran berdasarkan id.
     */
    public function show($id) {
        $payment = Payments::with('orderItems')->findOrFail($id);

        return new PaymentResource($payment);
    }

    /**
     * Membuat pembayaran baru.
     * Amount akan dihitung otomatis dari harga order + pajak US (misal 10%).
     */
    public function store(Request $request) {
        // Validasi input tanpa amount (amount dihitung otomatis)
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:order_items,id',
            'payment_method' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            // 'amount' tidak divalidasi dari request, akan dihitung otomatis
            'status' => 'required|in:pending,success,failed',
            'proof_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Ambil order terkait
        $order = OrderItems::find($request->order_id);
        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        // Hitung total harga order (price) + pajak US (misal 10%)
        $orderPrice = $order->price;
        $taxRate = 0.10; // 10% US sales tax
        $taxAmount = $orderPrice * $taxRate;
        $totalAmount = round($orderPrice + $taxAmount, 2);

        // Buat data pembayaran
        $paymentData = [
            'order_id' => $request->order_id,
            'payment_method' => $request->payment_method,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'amount' => $totalAmount,
            'status' => $request->status,
            'proof_url' => $request->proof_url,
        ];

        $payment = Payments::create($paymentData);

        return new PaymentResource($payment);
    }

    /**
     * Mengupdate data pembayaran berdasarkan id.
     * Amount akan dihitung ulang dari harga order + pajak US (misal 10%).
     */
    public function update(Request $request, $id) {
        $payment = Payments::findOrFail($id);

        // Validasi input tanpa amount (amount dihitung otomatis)
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:order_items,id',
            'payment_method' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            // 'amount' tidak divalidasi dari request, akan dihitung otomatis
            'status' => 'required|in:pending,paid,failed,expired',
            'proof_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Ambil order terkait
        $order = OrderItems::find($request->order_id);
        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        // Hitung total harga order (price) + pajak US (misal 10%)
        $orderPrice = $order->price;
        $taxRate = 0.10; // 10% US sales tax
        $taxAmount = $orderPrice * $taxRate;
        $totalAmount = round($orderPrice + $taxAmount, 2);

        // Update data pembayaran
        $payment->order_id = $request->order_id;
        $payment->payment_method = $request->payment_method;
        $payment->account_number = $request->account_number;
        $payment->account_name = $request->account_name;
        $payment->amount = $totalAmount;
        $payment->status = $request->status;
        $payment->proof_url = $request->proof_url;
        $payment->save();

        return new PaymentResource($payment);
    }

    /**
     * Menghapus data pembayaran berdasarkan id.
     */
    public function destroy($id) {
        $payment = Payments::findOrFail($id);
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully.'
        ], 200);
    }
}
