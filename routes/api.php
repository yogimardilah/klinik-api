<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PasienController;
use App\Http\Controllers\Api\DoctorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Klinik API is running',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    
    // Protected authentication routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    });
});

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Dashboard endpoints
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/activities', [DashboardController::class, 'activities']);
        Route::get('/notifications', [DashboardController::class, 'notifications']);
    });
    
    // Pasien (Patient) management
    Route::apiResource('pasien', PasienController::class);
    Route::prefix('pasien')->group(function () {
        Route::get('/export', [PasienController::class, 'export']);
        Route::post('/import', [PasienController::class, 'import']);
        Route::get('/search', [PasienController::class, 'search']);
        Route::get('/statistics', [PasienController::class, 'statistics']);
    });
    
    // Doctor management
    Route::apiResource('doctor', DoctorController::class);
    Route::prefix('doctor')->group(function () {
        Route::get('/{doctor}/schedule', [DoctorController::class, 'schedule']);
        Route::post('/{doctor}/schedule', [DoctorController::class, 'updateSchedule']);
        Route::get('/{doctor}/patients', [DoctorController::class, 'patients']);
        Route::get('/statistics', [DoctorController::class, 'statistics']);
    });
    
    // User management (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [AuthController::class, 'users']);
        Route::post('/users', [AuthController::class, 'createUser']);
        Route::put('/users/{user}', [AuthController::class, 'updateUser']);
        Route::delete('/users/{user}', [AuthController::class, 'deleteUser']);
        Route::post('/users/{user}/roles', [AuthController::class, 'assignRole']);
    });
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            'GET /api/health' => 'Health check',
            'POST /api/auth/login' => 'User login',
            'POST /api/auth/register' => 'User registration',
            'GET /api/dashboard/stats' => 'Dashboard statistics (auth required)',
            'GET /api/pasien' => 'List patients (auth required)',
            'GET /api/doctor' => 'List doctors (auth required)',
        ]
    ], 404);
});
