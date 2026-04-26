<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $products]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|url',
            'category' => 'nullable|string|max:100',
        ]);

        $product = Product::create($validated);

        return response()->json(['data' => $product], 201);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        return response()->json(['data' => $product]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'image_url' => 'sometimes|nullable|url',
            'category' => 'sometimes|nullable|string|max:100',
        ]);

        $product->update($validated);

        return response()->json(['data' => $product]);
    }

    public function destroy(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(null, 204);
    }

    // public function store(Request $request): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'category_id' => 'required|exists:categories,id',
    //         'name' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'price' => 'required|numeric|min:0',
    //         'stock_quantity' => 'required|integer|min:0',
    //         'min_stock_level' => 'required|integer|min:0',
    //         'sku' => 'required|string|max:100|unique:products,sku',
    //         'image_url' => 'nullable|url',
    //         'brand' => 'nullable|string|max:100',
    //         'part_number' => 'nullable|string|max:100|unique:products,part_number',
    //         'vehicle_model' => 'nullable|string|max:255',
    //         'compatibility' => 'nullable|string',
    //     ]);

    //     $product = Product::create($validated);

    //     return response()->json(['data' => $product], 201);
    // }

    // public function show(string $id): JsonResponse
    // {
    //     $product = Product::with('category')->findOrFail($id);
    //     return response()->json(['data' => $product]);
    // }

    // public function update(Request $request, string $id): JsonResponse
    // {
    //     $product = Product::findOrFail($id);

    //     $validated = $request->validate([
    //         'category_id' => 'sometimes|required|exists:categories,id',
    //         'name' => 'sometimes|required|string|max:255',
    //         'description' => 'sometimes|nullable|string',
    //         'price' => 'sometimes|required|numeric|min:0',
    //         'stock_quantity' => 'sometimes|required|integer|min:0',
    //         'min_stock_level' => 'sometimes|required|integer|min:0',
    //         'sku' => 'sometimes|required|string|max:100|unique:products,sku,'.$product->id,
    //         'image_url' => 'sometimes|nullable|url',
    //         'brand' => 'sometimes|nullable|string|max:100',
    //         'part_number' => 'sometimes|nullable|string|max:100|unique:products,part_number,'.$product->id,
    //         'vehicle_model' => 'sometimes|nullable|string|max:255',
    //         'compatibility' => 'sometimes|nullable|string',
    //     ]);

    //     $product->update($validated);

    //     return response()->json(['data' => $product]);
    // }

    // public function destroy(string $id): JsonResponse
    // {
    //     $product = Product::findOrFail($id);
    //     $product->delete();

    //     return response()->json(null, 204);
    // }
}

