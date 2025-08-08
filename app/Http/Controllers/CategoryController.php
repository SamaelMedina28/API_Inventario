<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $category = Category::orderBy('id', 'desc')->where('user_id', auth('api')->user()->id)->get();
        if ($category->isEmpty()) {
            return response()->json([
                'message' => 'No hay categorias',
            ], 404);
        }
        return response()->json([
            'categorias' => $category,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validaciones = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validaciones->fails()) {
            return response()->json($validaciones->errors(), 422);
        }

        $category = Category::create([
            'user_id' => auth('api')->user()->id,
            'name' => $request->name,
        ]);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        if ($category->user_id != auth('api')->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para ver esta categoria',
            ], 403);
        }
        return response()->json([
            'categoria' => $category,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        if ($category->user_id != auth('api')->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para actualizar esta categoria',
            ], 403);
        }
        $validaciones = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validaciones->fails()) {
            return response()->json($validaciones->errors(), 422);
        }

        $category->update([
            'user_id' => auth('api')->user()->id,
            'name' => $request->name,
        ]);

        return response()->json($category, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->user_id != auth('api')->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para eliminar esta categoria',
            ], 403);
        }
        $category->delete();

        return response()->json([
            'message' => 'Categoria eliminada correctamente',
        ], 200);
    }
}
