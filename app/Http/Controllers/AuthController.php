<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Registro
    public function register(Request $request)
    {
        $validaciones = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validaciones->fails()) {
            return response()->json([
                'errors' => $validaciones->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'user' => $user,
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $validaciones = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validaciones->fails()) {
            return response()->json([
                'errors' => $validaciones->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        try {
            $token = auth('api')->attempt($credentials);
            
            if (!$token) {
                return response()->json([
                    'message' => 'Credenciales inválidas',
                ], 401);
            }
            return response()->json([
                'token' => $token,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al iniciar sesión', $th->getMessage()
            ], 500);
        }
    }

    // Get user
    public function getUser()
    {
        $user = auth('api')->user();

        return response()->json([
            'user' => $user,
        ], 200);
    }

    // Logout
    public function logout()
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'Cierre de sesión exitoso',
            'status' => 200,
        ], 200);
    }
}
