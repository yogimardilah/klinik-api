<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Exports\PasienExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;  

class PasienController extends Controller
{
    /**
     * Display a listing of patients
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Pasien::query();

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nomor_rekam_medis', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('nomor_telepon', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Filter by gender
            if ($request->has('jenis_kelamin') && $request->jenis_kelamin) {
                $query->where('jenis_kelamin', $request->jenis_kelamin);
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $pasiens = $query->paginate($perPage);

            return response()->json($pasiens);
        } catch (\Exception $e) {
            Log::error('Failed to fetch patients', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Gagal mengambil data pasien',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created patient
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255',
                'tanggal_lahir' => 'required|date|before:today',
                'jenis_kelamin' => 'required|in:L,P',
                'alamat' => 'required|string|max:500',
                'nomor_telepon' => 'required|string|max:20',
                'email' => 'nullable|email|max:255|unique:pasiens,email',
                'golongan_darah' => 'nullable|in:A,B,AB,O',
                'status' => 'required|in:aktif,non-aktif',
                'nomor_rekam_medis' => 'nullable|string|max:100|unique:pasiens,nomor_rekam_medis',
                'alergi' => 'nullable|string|max:500',
                'kontak_darurat_nama' => 'nullable|string|max:255',
                'kontak_darurat_telepon' => 'nullable|string|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate nomor rekam medis if not provided
            if (!$request->nomor_rekam_medis) {
                $count = Pasien::count() + 1;
                $request->merge([
                    'nomor_rekam_medis' => 'RM-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT)
                ]);
            }

            $pasien = Pasien::create($request->all());

            return response()->json([
                'message' => 'Pasien berhasil ditambahkan',
                'data' => $pasien
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create patient', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Gagal menyimpan data pasien',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified patient
     */
    public function show($id): JsonResponse
    {
        try {
            $pasien = Pasien::findOrFail($id);
            return response()->json([
                'message' => 'Data pasien berhasil ditemukan',
                'data' => $pasien
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to fetch patient ID $id", ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Data pasien tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified patient
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $pasien = Pasien::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama' => 'sometimes|required|string|max:255',
                'tanggal_lahir' => 'sometimes|required|date|before:today',
                'jenis_kelamin' => 'sometimes|required|in:L,P',
                'alamat' => 'sometimes|required|string|max:500',
                'nomor_telepon' => 'sometimes|required|string|max:20',
                'email' => 'sometimes|nullable|email|max:255|unique:pasiens,email,' . $id,
                'golongan_darah' => 'sometimes|nullable|in:A,B,AB,O',
                'status' => 'sometimes|required|in:aktif,non-aktif',
                'nomor_rekam_medis' => 'sometimes|nullable|string|max:100|unique:pasiens,nomor_rekam_medis,' . $id,
                'alergi' => 'sometimes|nullable|string|max:500',
                'kontak_darurat_nama' => 'sometimes|nullable|string|max:255',
                'kontak_darurat_telepon' => 'sometimes|nullable|string|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pasien->update($request->all());

            return response()->json([
                'message' => 'Data pasien berhasil diperbarui',
                'data' => $pasien
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update patient ID $id", ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Gagal memperbarui data pasien',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified patient
     */
    public function destroy($id): JsonResponse
    {
        try {
            $pasien = Pasien::findOrFail($id);
            $pasien->delete();

            return response()->json([
                'message' => 'Data pasien berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to delete patient ID $id", ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Gagal menghapus data pasien',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export patients data
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $format = $request->get('format', 'excel');
            $pasiens = Pasien::all();

            if ($format === 'pdf') {
                // In a real implementation, you would generate PDF
                return response()->json([
                    'message' => 'PDF export will be implemented',
                    'download_url' => '/api/pasien/export/pdf'
                ]);
            } else {
                // In a real implementation, you would generate Excel file
                return response()->json([
                    'message' => 'Excel export will be implemented',
                    'download_url' => '/api/pasien/export/excel'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengekspor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import patients data
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls,csv'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // In a real implementation, you would process the import file
            return response()->json([
                'message' => 'Import functionality will be implemented',
                'imported_count' => 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengimpor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search patients
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            $limit = $request->get('limit', 10);

            if (empty($query)) {
                return response()->json([
                    'data' => [],
                    'message' => 'Query parameter is required'
                ]);
            }

            $pasiens = Pasien::where('nama', 'like', "%{$query}%")
                            ->orWhere('nomor_rekam_medis', 'like', "%{$query}%")
                            ->orWhere('email', 'like', "%{$query}%")
                            ->limit($limit)
                            ->get();

            return response()->json([
                'data' => $pasiens,
                'query' => $query,
                'count' => $pasiens->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mencari data pasien',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_patients' => Pasien::count(),
                'active_patients' => Pasien::where('status', 'aktif')->count(),
                'inactive_patients' => Pasien::where('status', 'non-aktif')->count(),
                'male_patients' => Pasien::where('jenis_kelamin', 'L')->count(),
                'female_patients' => Pasien::where('jenis_kelamin', 'P')->count(),
                'patients_today' => Pasien::whereDate('created_at', today())->count(),
                'patients_this_week' => Pasien::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'patients_this_month' => Pasien::whereMonth('created_at', now()->month)
                                              ->whereYear('created_at', now()->year)
                                              ->count(),
            ];

            // Blood type distribution
            $bloodTypes = Pasien::selectRaw('golongan_darah, COUNT(*) as count')
                               ->whereNotNull('golongan_darah')
                               ->groupBy('golongan_darah')
                               ->get();

            $stats['blood_type_distribution'] = $bloodTypes->mapWithKeys(function ($item) {
                return [$item->golongan_darah => $item->count];
            });

            return response()->json([
                'data' => $stats,
                'generated_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil statistik pasien',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Export ke Excel
    public function exportExcel()
    {
        return Excel::download(new PasienExport, 'data-pasien.xlsx');
    }
    
    // Export ke PDF
    public function exportPDF()
    {
        $pasiens = Pasien::all();
        $pdf = PDF::loadView('pdf.pasien', compact('pasiens'));
        return $pdf->download('data-pasien.pdf');
    }
}
