<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Products::with('categories')->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found'
            ], 404);
        }

        return response()->json(ProductResource::collection($products), 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Products::create([
            'category_id' => $request->input('category_id'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'stock' => $request->input('stock'),
            'image_url' => $request->input('image_url')
        ]);

        return response()->json(new ProductResource($product), 201);
    }

    public function show($id)
    {
        $product = Products::with('categories')->findOrFail($id);
        return new ProductResource($product);
    }

    public function update(Request $request, $id)
    {
        $product = Products::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'sometimes|integer|min:0',
            'image_url' => 'sometimes|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($request->all());

        return new ProductResource($product);
    }

    public function destroy($id)
    {
        $product = Products::findOrFail($id);
        $product->delete();

        return response()->json(null, 204);
    }

    /**
     * Mengurangi stok produk ketika dipesan
     *
     * @param int $productId ID produk
     * @param int $quantity Jumlah yang dipesan
     * @return \Illuminate\Http\JsonResponse
     */
    public function reduceStock($productId, $quantity)
    {
        $product = Products::findOrFail($productId);

        if ($product->stock < $quantity) {
            return response()->json([
                'message' => 'Insufficient stock',
                'available_stock' => $product->stock
            ], 400);
        }

        $product->stock -= $quantity;
        $product->save();

        return response()->json([
            'message' => 'Stock reduced successfully',
            'product' => new ProductResource($product)
        ], 200);
    }
}