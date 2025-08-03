<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderItemsResource;
use App\Models\OrderItems;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderItemsController extends Controller
{
    public function index()
    {
        $orderItems = OrderItems::with('users', 'products')->get();

        if ($orderItems->isEmpty()) {
            return response()->json([
                'message' => 'No Orders Found!'
            ], 404);
        }
        return response()->json(OrderItemsResource::collection($orderItems), 200);
    }

    public function show($id) {
        $orderItems = OrderItems::with('users', 'products')->findOrFail($id);
        return new OrderItemsResource($orderItems);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            // 'price' dihapus dari validasi, karena akan dihitung otomatis
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Mulai transaksi database
        return DB::transaction(function () use ($request) {
            // Dapatkan produk dan periksa stok
            $product = Products::findOrFail($request->product_id);

            if ($product->stock < $request->quantity) {
                return response()->json([
                    'message' => 'Insufficient stock',
                    'available_stock' => $product->stock
                ], 400);
            }

            // Hitung harga otomatis
            $price = $product->price * $request->quantity;

            // Kurangi stok produk
            $product->decrement('stock', $request->quantity);

            // Buat order item
            $orderItems = OrderItems::create([
                'user_id' => $request->input('user_id'),
                'product_id' => $request->input('product_id'),
                'quantity' => $request->input('quantity'),
                'price' => $price,
            ]);

            return response()->json(new OrderItemsResource($orderItems), 201);
        });
    }

    public function update(Request $request, $id) {
        $orderItem = OrderItems::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            // 'price' dihapus dari validasi, karena akan dihitung otomatis
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Update Failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Mulai transaksi database
        return DB::transaction(function () use ($request, $id) {
            $orderItem = OrderItems::findOrFail($id);
            $product = Products::findOrFail($request->product_id);

            // Hitung perubahan quantity
            $quantityChange = $request->quantity - $orderItem->quantity;

            // Periksa stok jika quantity bertambah
            if ($quantityChange > 0 && $product->stock < $quantityChange) {
                return response()->json([
                    'message' => 'Insufficient stock for the quantity change',
                    'available_stock' => $product->stock
                ], 400);
            }

            // Update stok produk
            $product->increment('stock', -$quantityChange);

            // Hitung harga otomatis
            $price = $product->price * $request->quantity;

            // Update order item
            $orderItem->update([
                'user_id' => $request->input('user_id'),
                'product_id' => $request->input('product_id'),
                'quantity' => $request->input('quantity'),
                'price' => $price,
            ]);

            return new OrderItemsResource($orderItem);
        });
    }

    public function destroy($id) {
        return DB::transaction(function () use ($id) {
            $orderItem = OrderItems::findOrFail($id);
            $product = Products::find($orderItem->product_id);

            // Kembalikan stok jika produk masih ada
            if ($product) {
                $product->increment('stock', $orderItem->quantity);
            }

            $orderItem->delete();
            return response()->json(null, 204);
        });
    }
}