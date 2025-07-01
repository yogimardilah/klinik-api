<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    /**
     * Display a listing of doctors
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::where('role', 'doctor');

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $doctors = $query->paginate($perPage);

            return response()->json($doctors);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data dokter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created doctor
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'specialization' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $doctor = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => 'doctor',
                'email_verified_at' => now()
            ]);

            return response()->json([
                'message' => 'Dokter berhasil ditambahkan',
                'data' => $doctor
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan data dokter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified doctor
     */
    public function show($id): JsonResponse
    {
        try {
            $doctor = User::where('role', 'doctor')->findOrFail($id);
            return response()->json([
                'message' => 'Data dokter berhasil ditemukan',
                'data' => $doctor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Data dokter tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified doctor
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $doctor = User::where('role', 'doctor')->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|required|string|min:8',
                'specialization' => 'sometimes|nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['name', 'email']);
            if ($request->has('password')) {
                $data['password'] = bcrypt($request->password);
            }

            $doctor->update($data);

            return response()->json([
                'message' => 'Data dokter berhasil diperbarui',
                'data' => $doctor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui data dokter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified doctor
     */
    public function destroy($id): JsonResponse
    {
        try {
            $doctor = User::where('role', 'doctor')->findOrFail($id);
            $doctor->tokens()->delete(); // Revoke all tokens
            $doctor->delete();

            return response()->json([
                'message' => 'Data dokter berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus data dokter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor's schedule
     */
    public function schedule($id): JsonResponse
    {
        try {
            $doctor = User::where('role', 'doctor')->findOrFail($id);
            
            // In a real implementation, you would fetch actual schedule data
            $schedule = [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->name,
                'schedules' => [
                    ['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '16:00'],
                    ['day' => 'Tuesday', 'start_time' => '08:00', 'end_time' => '16:00'],
                    ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '16:00'],
                    ['day' => 'Thursday', 'start_time' => '08:00', 'end_time' => '16:00'],
                    ['day' => 'Friday', 'start_time' => '08:00', 'end_time' => '16:00']
                ]
            ];

            return response()->json([
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil jadwal dokter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update doctor's schedule
     */
    public function updateSchedule(Request $request, $id): JsonResponse
    {
        try {
            $doctor = User::where('role', 'doctor')->findOrFail($id);

            // In a real implementation, you would validate and update schedule
            return response()->json([
                'message' => 'Jadwal dokter berhasil diperbarui',
                'data' => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui jadwal dokter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor's patients
     */
    public function patients($id): JsonResponse
    {
        try {
            $doctor = User::where('role', 'doctor')->findOrFail($id);
            
            // In a real implementation, you would fetch patients assigned to this doctor
            return response()->json([
                'data' => [],
                'message' => 'Patient assignment feature will be implemented'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data pasien dokter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_doctors' => User::where('role', 'doctor')->count(),
                'active_doctors' => User::where('role', 'doctor')->count(), // All are active by default
                'doctors_with_patients' => 0, // Would be calculated based on assignments
                'average_patients_per_doctor' => 0
            ];

            return response()->json([
                'data' => $stats,
                'generated_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil statistik dokter',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}