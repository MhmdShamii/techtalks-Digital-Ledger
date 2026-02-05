<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $storeId = $request->user()->store_id;

        $products = Product::where('store_id', $storeId)
            ->orderBy('id', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $products,
            'message' => 'Products fetched successfully.',
        ]);
    }

    public function store(Request $request)
    {
        $storeId = $request->user()->store_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:2048'],
            'quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        // Force store_id from token
        $validated['store_id'] = $storeId;
        $validated['quantity'] = $validated['quantity'] ?? 0;

        // Optional: unique code per store (if you didn't add DB unique constraint)
        if (!empty($validated['code'])) {
            $exists = Product::where('store_id', $storeId)
                ->where('code', $validated['code'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'data' => null,
                    'message' => 'This code already exists in your store.',
                ], 422);
            }
        }

        $product = Product::create($validated);

        return response()->json([
            'data' => $product,
            'message' => 'Product created successfully.',
        ], 201);
    }

    public function show(Request $request, Product $product)
    {
        $storeId = $request->user()->store_id;

        if ($product->store_id !== $storeId) {
            return response()->json([
                'data' => null,
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json([
            'data' => $product,
            'message' => 'Product fetched successfully.',
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $storeId = $request->user()->store_id;

        if ($product->store_id !== $storeId) {
            return response()->json([
                'data' => null,
                'message' => 'Product not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'nullable', 'string', 'max:255'],
            'image' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'quantity' => ['sometimes', 'integer', 'min:0'],
        ]);

        // Never allow changing store_id from request
        unset($validated['store_id']);

        // Optional uniqueness check for code per store
        if (array_key_exists('code', $validated) && !empty($validated['code'])) {
            $exists = Product::where('store_id', $storeId)
                ->where('code', $validated['code'])
                ->where('id', '!=', $product->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'data' => null,
                    'message' => 'This code already exists in your store.',
                ], 422);
            }
        }

        $product->update($validated);

        return response()->json([
            'data' => $product->fresh(),
            'message' => 'Product updated successfully.',
        ]);
    }

    public function destroy(Request $request, Product $product)
    {
        $storeId = $request->user()->store_id;

        if ($product->store_id !== $storeId) {
            return response()->json([
                'data' => null,
                'message' => 'Product not found.',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'data' => null,
            'message' => 'Product deleted successfully.',
        ]);
    }
}
