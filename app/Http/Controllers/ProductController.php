<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener todos los productos del usuario autenticado con posibles filtros
        $products = Product::with('category') 
            ->where('user_id', auth('api')->id()) 
            ->when($request->filled('search'), function ($query) use ($request) { 
                $query->where(function ($subQuery) use ($request) { 
                    $subQuery->where('name', 'LIKE', "%{$request->search}%")->orWhere('comment', 'LIKE', "%{$request->search}%"); 
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) { 
                $query->where('category_id', $request->category_id); 
            })
            ->orderByDesc('id') 
            ->paginate(10);

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron productos',
            ], 404);
        }

        return response()->json([
            'productos' => $products,
            'categorias' => Category::where('user_id', auth('api')->id())->get(),
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        if ($product->user_id != auth('api')->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para actualizar este producto',
            ], 403);
        }
        $validaciones = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric',
            'comment' => 'sometimes|nullable|string|max:500',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        if ($validaciones->fails()) {
            return response()->json($validaciones->errors(), 422);
        }

        $product->update($request->all());
        return response()->json([
            'producto' => $product,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->user_id != auth('api')->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para eliminar este producto',
            ], 403);
        }
        $product->delete();
        return response()->json([
            'message' => 'Producto eliminado correctamente',
        ], 200);
    }
}
