<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

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
            $token = JWTAuth::attempt($credentials);

            if (!$token) {
                return response()->json([
                    'message' => 'Credenciales inválidas',
                ], 401);
            }

            $user = JWTAuth::user();

            return response()->json([
                'user' => $user
            ], 200)->cookie(
                'auth_token',
                $token,
                config('jwt.ttl'),
                '/',
                null,
                true, // Secure
                true, // HttpOnly
                false,
                'Strict'
            );
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al iniciar sesión', $th->getMessage()
            ], 500);
        }
    }

    // Get user
    public function getUser(Request $request)
    {
        // Intentar obtener el token de la cookie
        $token = $request->cookie('auth_token');

        if (!$token) {
            return response()->json([
                'message' => 'Token no encontrado',
            ], 401);
        }

        try {
            // Establecer el token en el guard para que pueda obtener el usuario
            JWTAuth::setToken($token);
            $user = JWTAuth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado o token inválido',
                ], 401);
            }

            return response()->json([
                'user' => $user,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Token inválido o expirado',
            ], 401);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        $token = $request->cookie('auth_token');

        if ($token) {
            try {
                JWTAuth::setToken($token)->logout();
            } catch (\Throwable $th) {
                // Si hay error al hacer logout del token, continuamos para limpiar la cookie
            }
        }

        return response()->json([
            'message' => 'Cierre de sesión exitoso',
            'status' => 200,
        ], 200)->cookie(
            'auth_token',
            '', // Valor vacío
            -1, // Tiempo negativo para expirar inmediatamente
            '/',
            null,
            true,
            true,
            false,
            'Strict'
        );
    }

    // Método adicional para refrescar el token
    public function refresh(Request $request)
    {
        $token = $request->cookie('auth_token');

        if (!$token) {
            return response()->json([
                'message' => 'Token no encontrado',
            ], 401);
        }

        try {
            JWTAuth::setToken($token);
            $newToken = JWTAuth::refresh($token);

            return response()->json([
                'message' => 'Token refrescado exitosamente',
            ], 200)->cookie(
                'auth_token',
                $newToken,
                config('jwt.ttl'),
                '/',
                null,
                true,
                true,
                false,
                'Strict'
            );
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al refrescar token',
            ], 401);
        }
    }
}
