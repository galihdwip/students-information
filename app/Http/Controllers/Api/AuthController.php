<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Tymon\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    /**
     * Register a new user and generate a JWT token.
     */
    #[OA\Post(
        path: '/auth/register',
        summary: 'Register new user',
        description: 'Mendaftarkan user baru dan langsung mengembalikan JWT Bearer token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Registrasi berhasil'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $this->guard()->login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => $this->formatTokenResponse($token, $user),
        ], 201);
    }

    /**
     * Get a JWT via given credentials.
     */
    #[OA\Post(
        path: '/auth/login',
        summary: 'Login user',
        description: 'Login menggunakan email dan password untuk mendapatkan JWT token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login berhasil'),
            new OA\Response(response: 401, description: 'Kredensial tidak valid'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');
        $token = $this->guard()->attempt($credentials);

        if (! is_string($token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized / Invalid credentials',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => $this->formatTokenResponse($token, $this->guard()->user()),
        ]);
    }

    /**
     * Get the authenticated User profile.
     */
    #[OA\Get(
        path: '/auth/me',
        summary: 'Get current authenticated user profile',
        description: 'Mengambil data profil user yang sedang login',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Profil user berhasil diambil'),
            new OA\Response(response: 401, description: 'Unauthenticated / Token tidak valid'),
        ]
    )]
    public function me(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->guard()->user(),
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    #[OA\Post(
        path: '/auth/logout',
        summary: 'Logout user',
        description: 'Logout dan meng-invalidate JWT Bearer token saat ini',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Logout berhasil'),
            new OA\Response(response: 401, description: 'Unauthenticated / Token tidak valid'),
        ]
    )]
    public function logout(): JsonResponse
    {
        $this->guard()->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Refresh a token.
     */
    #[OA\Post(
        path: '/auth/refresh',
        summary: 'Refresh JWT token',
        description: 'Memperbarui token JWT yang masih valid dengan token baru',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Token berhasil diperbarui'),
            new OA\Response(response: 401, description: 'Token expired atau invalid'),
        ]
    )]
    public function refresh(): JsonResponse
    {
        $token = $this->guard()->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Token refreshed successfully',
            'data' => $this->formatTokenResponse($token, $this->guard()->user()),
        ]);
    }

    /**
     * Helper array structure for token responses.
     *
     * @return array<string, mixed>
     */
    protected function formatTokenResponse(string $token, mixed $user = null): array
    {
        return [
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => $this->guard()->factory()->getTTL() * 60,
            ],
        ];
    }

    protected function guard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }
}
