<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private function imageValidationRules(Request $request): array
    {
        if ($request->hasFile('image')) {
            return ['image' => 'nullable|file|image|mimes:jpeg,jpg,png,webp,gif|max:5120'];
        }

        return ['image' => 'nullable|string|max:2048'];
    }

    private function storeUploadedImage(Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        $imagePath = $request->file('image')->store('products', 'public');

        return url(Storage::url($imagePath));
    }

    private function deleteStoredImageIfLocal(?string $image): void
    {
        if (!$image || !str_contains($image, '/storage/')) {
            return;
        }

        $path = parse_url($image, PHP_URL_PATH) ?? $image;
        $relative = ltrim(str_replace('/storage/', '', $path), '/');

        if ($relative !== '') {
            Storage::disk('public')->delete($relative);
        }
    }

    public function index()
    {
        $products = Product::with('category')->where('status', 'active')->get();
        return response()->json($products);
    }

    /** All products for admin (active + inactive). */
    public function adminIndex()
    {
        $products = Product::with('category')->orderBy('name')->get();
        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with('category')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ], $this->imageValidationRules($request)));

        if ($uploaded = $this->storeUploadedImage($request)) {
            $validated['image'] = $uploaded;
        } elseif (empty($validated['image'] ?? null)) {
            unset($validated['image']);
        }

        $product = Product::create($validated);
        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validate(array_merge([
            'category_id' => 'sometimes|required|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'status' => 'sometimes|required|in:active,inactive',
        ], $this->imageValidationRules($request)));

        if ($uploaded = $this->storeUploadedImage($request)) {
            $this->deleteStoredImageIfLocal($product->image);
            $validated['image'] = $uploaded;
        } elseif (array_key_exists('image', $validated) && empty($validated['image'])) {
            unset($validated['image']);
        }

        $product->update($validated);
        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $this->deleteStoredImageIfLocal($product->image);

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function updateStock(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $product->update(['stock' => $validated['stock']]);
        return response()->json([
            'message' => 'Stock updated successfully',
            'data' => $product
        ]);
    }
}
