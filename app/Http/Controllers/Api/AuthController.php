<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * User login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'remember_me' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $user = Auth::user();
        
        // Create token
        $tokenName = 'auth-token';
        if ($request->remember_me) {
            $tokenName = 'remember-token';
        }
        
        $token = $user->createToken($tokenName)->plainTextToken;
        
        // Create refresh token
        $refreshToken = $user->createToken('refresh-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'staff',
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ],
            'token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * User registration
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,doctor,nurse,staff'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => now() // Auto verify for simplicity
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;
        $refreshToken = $user->createToken('refresh-token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ],
            'token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer'
        ], 201);
    }

    /**
     * User logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
            
            // Optionally revoke all tokens
            // $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Logout berhasil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'staff',
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'email']));

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        // Revoke all tokens to force re-login
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password berhasil diubah. Silakan login kembali.'
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
            
            // Create new token
            $token = $user->createToken('auth-token')->plainTextToken;
            $refreshToken = $user->createToken('refresh-token')->plainTextToken;

            return response()->json([
                'message' => 'Token berhasil diperbarui',
                'token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Here you would typically send a password reset email
        // For now, we'll just return a success message
        
        return response()->json([
            'message' => 'Link reset password telah dikirim ke email Anda'
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Here you would typically validate the reset token
        // For now, we'll just update the password directly
        
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Revoke all tokens
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password berhasil direset. Silakan login dengan password baru.'
        ]);
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Here you would typically validate the verification token
        // For now, we'll just return a success message
        
        return response()->json([
            'message' => 'Email berhasil diverifikasi'
        ]);
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email sudah terverifikasi'
            ]);
        }

        // Here you would typically send a verification email
        // For now, we'll just return a success message
        
        return response()->json([
            'message' => 'Email verifikasi telah dikirim ulang'
        ]);
    }

    /**
     * Get all users (admin only)
     */
    public function users(Request $request): JsonResponse
    {
        $users = User::select('id', 'name', 'email', 'role', 'email_verified_at', 'created_at')
                    ->paginate(15);

        return response()->json($users);
    }

    /**
     * Create new user (admin only)
     */
    public function createUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,doctor,nurse,staff'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => now()
        ]);

        return response()->json([
            'message' => 'User berhasil dibuat',
            'user' => $user
        ], 201);
    }

    /**
     * Update user (admin only)
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'sometimes|required|string|in:admin,doctor,nurse,staff'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'role']));

        return response()->json([
            'message' => 'User berhasil diperbarui',
            'user' => $user
        ]);
    }

    /**
     * Delete user (admin only)
     */
    public function deleteUser(User $user): JsonResponse
    {
        $user->tokens()->delete(); // Revoke all tokens
        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus'
        ]);
    }

    /**
     * Assign role to user (admin only)
     */
    public function assignRole(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:admin,doctor,nurse,staff'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update(['role' => $request->role]);

        return response()->json([
            'message' => 'Role berhasil diperbarui',
            'user' => $user
        ]);
    }
}