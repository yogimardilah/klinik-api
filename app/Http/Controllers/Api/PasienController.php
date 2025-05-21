<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PasienService;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exports\PasienExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;  


class PasienController extends Controller
{
    protected $pasienService;

    public function __construct(PasienService $pasienService)
    {
        $this->pasienService = $pasienService;
    }

    // List semua pasien dengan pagination dan pencarian
     public function index(Request $request)
    {
        try {
            $search = $request->search ?? null;
            $perPage = $request->per_page ?? 10;

            $pasiens = $this->pasienService->getPasiens($search, $perPage);

            return response()->json($pasiens);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data pasien', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal mengambil data pasien'], 500);
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
    // Simpan data pasien baru
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama' => 'required|string|max:255',
                'nik' => 'nullable|string|max:50',
                'alamat' => 'nullable|string|max:255',
                'no_hp' => 'nullable|string|max:20',
                'tanggal_lahir' => 'nullable|date',
                'jenis_kelamin' => 'nullable|in:L,P',
            ]);
            
            $pasien = Pasien::create($validated);
            
            return response()->json([
                'message' => 'Pasien berhasil ditambahkan',
                'data' => $pasien
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data pasien', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal menyimpan data pasien'], 500);
        }
    }
    
    // Detail pasien berdasarkan ID
    public function show($id)
    {
        try {
            $pasien = Pasien::findOrFail($id);
            return response()->json([
                'message' => 'Data pasien berhasil ditemukan',
                'data' => $pasien
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal mengambil data pasien ID $id", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Data pasien tidak ditemukan'], 404);
        }
    }
    
    // Update data pasien
    public function update(Request $request, $id)
    {
        try {
            $pasien = Pasien::findOrFail($id);
            
            $validated = $request->validate([
                'nama' => 'sometimes|required|string|max:255',
                'nik' => 'nullable|string|max:50',
                'alamat' => 'nullable|string|max:255',
                'no_hp' => 'nullable|string|max:20',
                'tanggal_lahir' => 'nullable|date',
                'jenis_kelamin' => 'nullable|in:L,P',
            ]);
            
            $pasien->update($validated);
            
            return response()->json([
                'message' => 'Data pasien berhasil diperbarui',
                'data' => $pasien
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal mengupdate data pasien ID $id", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal memperbarui data pasien'], 500);
        }
    }
    
    // Hapus pasien
    public function destroy($id)
    {
        try {
            $pasien = Pasien::findOrFail($id);
            $pasien->delete();
            
            return response()->json(['message' => 'Data pasien berhasil dihapus'], 200);
        } catch (\Exception $e) {
            Log::error("Gagal menghapus data pasien ID $id", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal menghapus data pasien'], 500);
        }
    }
}
