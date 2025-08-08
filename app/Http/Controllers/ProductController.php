<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $product = Product::orderBy('id', 'desc')->where('user_id', auth('api')->user()->id)->with('category')->get();
        if ($product->isEmpty()) {
            return response()->json([
                'message' => 'No hay productos',
            ], 404);
        }
        return response()->json([
            'productos' => $product,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validaciones
        $validaciones = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validaciones->fails()) {
            return response()->json($validaciones->errors(), 422);
        }

        $product = Product::create([
            'user_id' => auth('api')->user()->id,
            'name' => $request->name,
            'price' => $request->price,
            'category_id' => $request->category_id,
        ]);

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        if ($product->user_id != auth('api')->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para ver este producto',
            ], 403);
        }
        return response()->json([
            'producto' => $product,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
