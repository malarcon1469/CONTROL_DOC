<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Maneja la solicitud de login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'sometimes|string', // Opcional, para nombrar el token (útil para móviles)
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')], // Mensaje genérico de error de autenticación
            ]);
        }

        // Verificar si el usuario está activo (si tienes un campo 'activo' o 'email_verified_at' que uses para esto)
        // Ejemplo si tienes un campo 'activo' en tu modelo User y quieres verificarlo:
        // if (!$user->activo) {
        //     throw ValidationException::withMessages([
        //         'email' => ['Tu cuenta está inactiva. Contacta al administrador.'],
        //     ]);
        // }

        $deviceName = $request->post('device_name', $request->userAgent());
        $token = $user->createToken($deviceName); // Puedes pasar un nombre para el token

        return response()->json([
            'message' => 'Login exitoso.',
            'user' => [ // Devolver información básica del usuario es común
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(), // Devuelve los nombres de los roles del usuario
            ],
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
        ]);
    }

    /**
     * Maneja la solicitud de logout (revocación del token actual).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Revocar el token que se usó para autenticar la solicitud actual
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout exitoso.']);
    }

    /**
     * Obtener la información del usuario autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            // Podrías incluir más información del usuario o relaciones si es necesario
            // 'empresa_contratista' => $user->empresaContratistaAdministrada, // Ejemplo si es un ContratistaAdmin
        ]);
    }
}